<?php

namespace App\Modules\Estate\Controllers\Seeker;

use App\Core\Controllers\BaseWebController;
use App\Modules\Estate\Models\SeekerModel;
use App\Modules\Estate\Models\MessageModel;
use App\Modules\Estate\Models\SubscriptionModel;
use App\Modules\Estate\Models\SavedSearchModel;
use App\Modules\Estate\Config\Estate;

/**
 * Seeker panel — protected by session check (estate_seeker_id).
 * Displays: messages, saved searches, subscription status.
 */
class SeekerPanelController extends BaseWebController
{
    private SeekerModel       $seekers;
    private MessageModel      $messages;
    private SubscriptionModel $subscriptions;
    private SavedSearchModel  $savedSearches;
    private Estate            $config;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->seekers       = new SeekerModel();
        $this->messages      = new MessageModel();
        $this->subscriptions = new SubscriptionModel();
        $this->savedSearches = new SavedSearchModel();
        $this->config        = config(Estate::class);
    }

    /** GET /seeker/panel */
    public function index(): string|\CodeIgniter\HTTP\RedirectResponse
    {
        $seeker = $this->requireSeeker();
        if ($seeker instanceof \CodeIgniter\HTTP\RedirectResponse) {
            return $seeker;
        }

        $messages    = $this->messages->getForSeeker($seeker['id']);
        $unread      = $this->messages->countUnreadForSeeker($seeker['id']);
        $searches    = $this->savedSearches->getForSeeker($seeker['id']);
        $sub         = $this->subscriptions->findActiveForSeeker($seeker['id']);

        // Mark all messages as read when panel is opened
        foreach ($messages as $m) {
            if (! $m['read_at']) {
                $this->messages->markRead($m['id']);
            }
        }

        $this->setSeo(['title' => 'Mein Bereich — modeiland', 'robots' => 'noindex']);

        return $this->render('App\Modules\Estate\Views\seeker\panel', [
            'seeker'          => $seeker,
            'messages'        => $messages,
            'unread'          => $unread,
            'savedSearches'   => $searches,
            'subscription'    => $sub,
            'billingEnabled'  => $this->config->billingEnabled,
            'pollingInterval' => $this->config->messagePollingIntervalSeconds,
        ]);
    }

    /** GET /seeker/messages/poll — JSON for JS polling */
    public function poll(): \CodeIgniter\HTTP\ResponseInterface
    {
        $seekerId = (int) session()->get('estate_seeker_id');
        $unread   = $seekerId ? $this->messages->countUnreadForSeeker($seekerId) : 0;

        return $this->response
            ->setContentType('application/json')
            ->setBody(json_encode(['unread' => $unread]));
    }

    /** GET /seeker/logout */
    public function logout(): \CodeIgniter\HTTP\RedirectResponse
    {
        session()->remove('estate_seeker_id');
        session()->remove('estate_checkout_email');
        return redirect()->to(site_url('inserate'))->with('info', 'Sie wurden abgemeldet.');
    }

    // ─── Private helpers ─────────────────────────────────────────────────────

    private function requireSeeker(): array|\CodeIgniter\HTTP\RedirectResponse
    {
        $id = session()->get('estate_seeker_id');
        if (! $id) {
            return redirect()->to(site_url('abonnieren'))
                             ->with('info', 'Bitte melden Sie sich an oder abonnieren Sie, um Ihren Bereich zu sehen.');
        }

        $seeker = $this->seekers->find((int) $id);
        if (! $seeker) {
            session()->remove('estate_seeker_id');
            return redirect()->to(site_url('abonnieren'));
        }

        return $seeker;
    }
}
