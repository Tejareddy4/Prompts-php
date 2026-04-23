<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Csrf;
use App\Core\Database;
use App\Models\Interaction;
use App\Models\Prompt;
use App\Services\PromptService;

class PromptController extends Controller
{
    public function show(array $params): void
    {
        $db          = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $interaction = new Interaction($db);

        $prompt = $promptModel->findBySlug($params['slug'], Auth::id());
        if (!$prompt) {
            http_response_code(404);
            $this->render('errors/404', ['pageTitle' => 'Prompt Not Found']);
            return;
        }

        $interaction->addView((int)$prompt['id'], hash('sha256', session_id()), Auth::id());

        $this->render('prompts/show', [
            'prompt'          => $prompt,
            'pageTitle'       => $prompt['title'],
            'metaDescription' => substr($prompt['description'], 0, 155),
        ]);
    }

    public function createForm(): void
    {
        $this->render('prompts/create', ['pageTitle' => 'Submit a Prompt']);
    }

    public function store(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            flash('Session expired, please try again.', 'warning');
            $this->redirect('/prompts/create');
        }

        $service = $this->makeService();
        $result  = $service->submit($_POST, $_FILES['image'] ?? null, (int) Auth::id());

        if (!$result['ok']) {
            flash($result['error'], 'error');
            $this->redirect('/prompts/create');
        }

        flash('Your prompt has been submitted for review.', 'success');
        $this->redirect('/dashboard');
    }

    public function editForm(array $params): void
    {
        $db          = Database::connection($this->config['db']);
        $promptModel = new Prompt($db);
        $prompt      = $promptModel->findByIdForUser((int)$params['id'], (int)Auth::id());

        if (!$prompt) {
            flash('Prompt not found or permission denied.', 'error');
            $this->redirect('/dashboard');
        }

        $this->render('prompts/edit', ['prompt' => $prompt, 'pageTitle' => 'Edit Prompt']);
    }

    public function update(array $params): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            flash('Session expired, please try again.', 'warning');
            $this->redirect('/dashboard');
        }

        $service = $this->makeService();
        $result  = $service->edit((int)$params['id'], (int)Auth::id(), $_POST, $_FILES['image'] ?? null);

        if (!$result['ok']) {
            flash($result['error'], 'error');
            $this->redirect('/prompts/' . $params['id'] . '/edit');
        }

        flash('Prompt updated and re-submitted for review.', 'success');
        $this->redirect('/dashboard');
    }

    public function like(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null) || !Auth::id()) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }
        $result = $this->makeService()->recordInteractionAndRefreshScore('like', (int)$_POST['prompt_id'], Auth::id());
        $this->json($result);
    }

    public function save(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null) || !Auth::id()) {
            $this->json(['error' => 'Unauthorized'], 401);
            return;
        }
        $result = $this->makeService()->recordInteractionAndRefreshScore('save', (int)$_POST['prompt_id'], Auth::id());
        $this->json($result);
    }

    public function copy(): void
    {
        if (!Csrf::validate($_POST['_csrf'] ?? null)) {
            $this->json(['error' => 'Invalid token'], 419);
            return;
        }
        $result = $this->makeService()->recordInteractionAndRefreshScore('copy', (int)$_POST['prompt_id'], Auth::id());
        $this->json(['count' => $result['count']]);
    }

    private function makeService(): PromptService
    {
        $db = Database::connection($this->config['db']);
        return new PromptService(
            new Prompt($db),
            new Interaction($db),
            $this->config['upload'],
        );
    }
}
