<?php

namespace App\Notifications\Manuscript;

use Illuminate\Notifications\Messages\MailMessage as Base;

final class MailMessage extends Base
{
    public $actions = [];

    // ->action($action_text, $action_url)

    public function action($text, $url, $theme = 'primary')
    {
        
        $this->actionText = $text;
        $this->actionUrl = $url;

        return $this;
    }
}
