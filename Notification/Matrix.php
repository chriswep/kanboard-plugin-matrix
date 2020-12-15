<?php

namespace Kanboard\Plugin\Matrix\Notification;

use Kanboard\Core\Base;
use Kanboard\Core\Notification\NotificationInterface;

/**
 * Matrix Notification
 *
 * @package  notification
 * @author   Frederic Guillot
 */
class Matrix extends Base implements NotificationInterface
{
    /**
     * Send notification to a user
     *
     * @access public
     * @param  array     $user
     * @param  string    $eventName
     * @param  array     $eventData
     */
    public function notifyUser(array $user, $eventName, array $eventData)
    {
    }

    /**
     * Send notification to a project
     *
     * @access public
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     */
    public function notifyProject(array $project, $event_name, array $event_data)
    {
        $webhook = $this->projectMetadataModel->get($project['id'], 'matrix_webhook_url', $this->configModel->get('matrix_webhook_url'));
        $channel = $this->projectMetadataModel->get($project['id'], 'matrix_webhook_channel');

        if (! empty($webhook)) {
            $this->sendMessage($webhook, $channel, $project, $event_name, $event_data);
        }
    }

    /**
     * Get message to send
     *
     * @access public
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     * @return array
     */
    public function getMessage(array $project, $event_name, array $event_data)
    {
        if ($this->userSession->isLogged()) {
            $author = $this->helper->user->getFullname();
        } else if(!empty($event_data['user_id'])) {
            $eventUser = $this->userModel->getById($event_data['user_id']);
            $author = $this->helper->user->getFullname($eventUser);
        }
        if(!empty(@$author)) $title = $this->notificationModel->getTitleWithAuthor($author, $event_name, $event_data);
        else $title = $this->notificationModel->getTitleWithoutAuthor($event_name, $event_data);
        
        $message = '<strong>['.$project['name']."]</strong> &ndash; ";
        $message .= '<a href="'.$this->helper->url->to('TaskViewController', 'show', array('task_id' => $event_data['task']['id'], 'project_id' => $project['id']), '', true).'">';
        $message .= '<strong>'.$event_data['task']['title']."</strong>";
        $message .= '</a><br /><em>';
        $message .= $title;
        $message .= '</em>';

        return array(
            'text' => $message,
            'format' => 'html',
            'displayName' => 'Kanboard',
            // 'avatarUrl' => 'LINK_TO_PNG',
        );
    }

    /**
     * Send message to Matrix
     *
     * @access private
     * @param  string    $webhook
     * @param  string    $channel
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     */
    private function sendMessage($webhook, $channel, array $project, $event_name, array $event_data)
    {
        $payload = $this->getMessage($project, $event_name, $event_data);

        if (! empty($channel)) {
            $payload['channel'] = $channel;
        }

        $this->httpClient->postJsonAsync($webhook, $payload);
    }
}
