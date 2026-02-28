<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Interaction;
use App\Models\Prompt;

class PromptController extends Controller
{
    public function show(array $params): void
    {
        $db = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $interaction = new Interaction($db);

        $prompt = $promptModel->findBySlug($params['slug'], Auth::id());
        if (!$prompt) {
            http_response_code(404);
            echo 'Prompt not found';
            return;
        }

        $interaction->addView((int)$prompt['id'], hash('sha256', session_id()), Auth::id());

        $this->render('prompts/show', [
            'prompt' => $prompt,
            'pageTitle' => $prompt['title'],
            'metaDescription' => substr($prompt['description'], 0, 155),
        ]);
    }

    public function createForm(): void
    {
        $this->render('prompts/create', ['pageTitle' => 'Submit Prompt']);
    }

    public function store(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $this->redirect('/prompts/create');
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $promptText = trim($_POST['prompt_text'] ?? '');

        if ($title === '' || $promptText === '') {
            $_SESSION['flash'] = 'Title and prompt text are required.';
            $this->redirect('/prompts/create');
        }

        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-')) . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        $imagePath = $this->handleImageUpload($_FILES['image'] ?? null);

        $db = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $promptModel->create([
            'user_id' => Auth::id(),
            'title' => $title,
            'slug' => $slug,
            'description' => $description,
            'prompt_text' => $promptText,
            'image_path' => $imagePath,
        ]);

        $_SESSION['flash'] = 'Prompt submitted for review.';
        $this->redirect('/dashboard');
    }

    public function like(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null) || !Auth::id()) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::connection($this->config['db']);
        $interaction = new Interaction($db);
        $result = $interaction->toggleLike((int)$_POST['prompt_id'], Auth::id());
        $this->json($result);
    }

    public function save(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null) || !Auth::id()) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }

        $db = Database::connection($this->config['db']);
        $interaction = new Interaction($db);
        $result = $interaction->toggleSave((int)$_POST['prompt_id'], Auth::id());
        $this->json($result);
    }

    public function copy(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $this->json(['error' => 'Invalid token'], 419);
            return;
        }

        $db = Database::connection($this->config['db']);
        $interaction = new Interaction($db);
        $count = $interaction->addCopy((int)$_POST['prompt_id'], Auth::id());
        $this->json(['count' => $count]);
    }

    private function handleImageUpload(?array $file): ?string
    {
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            return null;
        }

        if ($file['size'] > $this->config['upload']['max_size']) {
            return null;
        }

        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        if (!in_array($mime, $this->config['upload']['allowed_types'], true)) {
            return null;
        }

        $extension = match ($mime) {
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            default => 'webp',
        };

        $filename = uniqid('prompt_', true) . '.' . $extension;
        $destination = $this->config['upload']['dir'] . '/' . $filename;

        if ($mime === 'image/jpeg') {
            $image = imagecreatefromjpeg($file['tmp_name']);
            imagejpeg($image, $destination, 75);
        } elseif ($mime === 'image/png') {
            $image = imagecreatefrompng($file['tmp_name']);
            imagepng($image, $destination, 6);
        } else {
            $image = imagecreatefromwebp($file['tmp_name']);
            imagewebp($image, $destination, 75);
        }

        if (isset($image) && $image !== false) {
            imagedestroy($image);
        }

        return '/assets/uploads/' . $filename;
    }
}
