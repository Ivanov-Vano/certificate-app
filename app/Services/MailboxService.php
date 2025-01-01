<?php

namespace App\Services;

use PhpImap\IncomingMail;
use PhpImap\Mailbox;

class MailboxService
{

    /**
     * @param string $folder
     */
    public function __construct(string $folder = 'INBOX')
    {
        $config = config('mailbox');
        $imapPath = sprintf('{%s:%s/%s}%s', $config['host'], $config['port'], $config['encryption'], $folder);
        $this->mailbox = new Mailbox(
            $imapPath,
            $config['username'],
            $config['password'],
            null, // attachmentsDir
            'UTF-8', // serverEncoding
            true, // trimImapPath
            false // attachmentFilenameMode
        );
        $this->folder = $folder;

    }

    public function getMessageBody(): array
    {
        $mailsIds = $this->mailbox->searchMailbox('UNSEEN');
        if (!$mailsIds) {
            return [];
        }

        $contents = [];

        foreach ($mailsIds as $mailId) {
            $mail = $this->mailbox->getMail($mailId);
            $contents[] = $this->getMailBody($mail);
            // Пометить письмо как непрочитанное, если оно не от покупателя
            $this->mailbox->markMailAsUnread($mailId);
        }

        return $contents;

    }

    protected function getMailBody(IncomingMail $mail)
    {
        // Получение текстового тела письма
        if (!empty($mail->textPlain)) {
            return $mail->textPlain;
        }

        // Если текстового тела нет, возвращаем HTML-тело
        if (!empty($mail->textHtml)) {
            return $mail->textHtml;
        }

        return '';
    }
}
