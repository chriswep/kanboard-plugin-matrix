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

        if (! empty($webhook)) {
            $this->sendMessage($webhook, $project, $event_name, $event_data);
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
        if($event_name === 'comment.create' && !empty($event_data['comment']['user_id'])) {
            $eventUser = $this->userModel->getById($event_data['comment']['user_id']);
            $author = $this->helper->user->getFullname($eventUser);
            // convert linebreaks to html
            $commentBody = str_replace(array("\r\n", "\n", "\r"), '<br>', $event_data['comment']['comment']);
            // convert markdown links to html
            $commentBody = preg_replace('/\[(.*)\]\((.*)\)/', '<a href="$2" target="_blank">$1</a>', $commentBody);
            // test for commit references
            if(preg_match('/(refs|closes|implements|fixes) #([0-9]*)/', $commentBody)) $body = $commentBody;
            else $body = '<em>'.$author. ' commented: </em>'.$commentBody;
        } else {
            if ($this->userSession->isLogged()) {
                $author = $this->helper->user->getFullname();
            } else if(!empty($event_data['user_id'])) {
                $eventUser = $this->userModel->getById($event_data['user_id']);
                $author = $this->helper->user->getFullname($eventUser);
            }
            
            if(!empty(@$author)) $body = $this->notificationModel->getTitleWithAuthor($author, $event_name, $event_data);
            else $body = $this->notificationModel->getTitleWithoutAuthor($event_name, $event_data);
            $body = '<em>'.$body.'</em>';
        }
            
        $message = '<strong>['.$project['name']."]</strong> &ndash; ";
        $message .= '<a href="'.$this->helper->url->to('TaskViewController', 'show', array('task_id' => $event_data['task']['id'], 'project_id' => $project['id']), '', true).'">';
        $message .= '<strong>'.$event_data['task']['title']."</strong>";
        $message .= '</a><br>';
        $message .= $body;

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
     * @param  array     $project
     * @param  string    $event_name
     * @param  array     $event_data
     */
    private function sendMessage($webhook, array $project, $event_name, array $event_data)
    {
        $payload = $this->getMessage($project, $event_name, $event_data);

        $this->httpClient->postJsonAsync($webhook, $payload);
    }
}
