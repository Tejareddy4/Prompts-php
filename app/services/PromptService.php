<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Category;
use App\Models\Interaction;
use App\Models\Prompt;
use PDO;

class PromptService
{
    public function __construct(
        private readonly Prompt      $promptModel,
        private readonly Interaction $interaction,
        private readonly Category    $categoryModel,
        private readonly array       $uploadConfig,
    ) {}

    public function submit(array $postData, ?array $file, int $userId): array
    {
        $title       = trim($postData['title'] ?? '');
        $description = trim($postData['description'] ?? '');
        $promptText  = trim($postData['prompt_text'] ?? '');

        if ($title === '') {
            return ['ok' => false, 'error' => 'Prompt title is required.'];
        }
        if ($promptText === '') {
            return ['ok' => false, 'error' => 'Prompt text is required.'];
        }

        $categoryId = $this->resolveCategoryId($postData['category_id'] ?? null);
        if ($categoryId === null) {
            return ['ok' => false, 'error' => 'Please choose a category for your prompt.'];
        }

        $slug      = $this->makeSlug($title);
        $imagePath = $this->handleUpload($file);
        if ($imagePath === null) {
            return ['ok' => false, 'error' => 'A preview image is required (JPG, PNG or WebP, max 5 MB).'];
        }

        $id = $this->promptModel->create([
            'user_id'     => $userId,
            'category_id' => $categoryId,
            'title'       => $title,
            'slug'        => $slug,
            'description' => $description,
            'prompt_text' => $promptText,
            'image_path'  => $imagePath,
        ]);

        return ['ok' => true, 'id' => $id];
    }

    public function edit(int $promptId, int $userId, array $postData, ?array $file): array
    {
        $prompt = $this->promptModel->findByIdForUser($promptId, $userId);
        if (!$prompt) {
            return ['ok' => false, 'error' => 'Prompt not found or permission denied.'];
        }

        $title      = trim($postData['title'] ?? '');
        $promptText = trim($postData['prompt_text'] ?? '');
        if ($title === '' || $promptText === '') {
            return ['ok' => false, 'error' => 'Title and prompt text are required.'];
        }

        $categoryId = $this->resolveCategoryId($postData['category_id'] ?? null);
        if ($categoryId === null) {
            return ['ok' => false, 'error' => 'Please choose a category for your prompt.'];
        }

        $imagePath = $prompt['image_path'];
        $newImage  = $this->handleUpload($file);
        if ($newImage !== null) {
            $imagePath = $newImage;
        }
        if ($imagePath === null || $imagePath === '') {
            return ['ok' => false, 'error' => 'A preview image is required (JPG, PNG or WebP, max 5 MB).'];
        }

        $this->promptModel->update($promptId, [
            'title'       => $title,
            'category_id' => $categoryId,
            'description' => trim($postData['description'] ?? ''),
            'prompt_text' => $promptText,
            'image_path'  => $imagePath,
        ]);

        return ['ok' => true];
    }

    private function resolveCategoryId(mixed $raw): ?int
    {
        if (!is_numeric($raw)) {
            return null;
        }
        $id = (int) $raw;
        return $this->categoryModel->findById($id) ? $id : null;
    }

    public function recordInteractionAndRefreshScore(string $type, int $promptId, ?int $userId): array
    {
        $result = match ($type) {
            'like' => $this->interaction->toggleLike($promptId, $userId),
            'save' => $this->interaction->toggleSave($promptId, $userId),
            'copy' => ['count' => $this->interaction->addCopy($promptId, $userId)],
            default => throw new \InvalidArgumentException("Unknown interaction: {$type}"),
        };

        // Refresh trending score asynchronously-ish (cheap single UPDATE)
        $this->promptModel->refreshTrendingScore($promptId);

        return $result;
    }

    private function makeSlug(string $title): string
    {
        return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'))
             . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
    }

    private function handleUpload(?array $file): ?string
    {
        if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }
        if ($file['size'] > $this->uploadConfig['max_size']) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime  = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $this->uploadConfig['allowed_types'], true)) {
            return null;
        }

        $ext  = match ($mime) { 'image/jpeg' => 'jpg', 'image/png' => 'png', default => 'webp' };
        $dest = $this->uploadConfig['dir'] . '/' . uniqid('p_', true) . '.' . $ext;

        $img = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($file['tmp_name']),
            'image/png'  => imagecreatefrompng($file['tmp_name']),
            default      => imagecreatefromwebp($file['tmp_name']),
        };

        if ($img === false) {
            return null;
        }

        match ($mime) {
            'image/jpeg' => imagejpeg($img, $dest, 75),
            'image/png'  => imagepng($img, $dest, 6),
            default      => imagewebp($img, $dest, 75),
        };
        imagedestroy($img);

        return '/assets/uploads/' . basename($dest);
    }
}
