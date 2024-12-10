<?php

declare(strict_types=1);

return [
    /**
     * Dotkernel mail module configuration
     * Note that many of these options can be set programmatically too, when sending mail messages actually that is
     * what you'll usually do, these configs provide just defaults and options that remain the same for all mails
     */
    'dot_mail' => [
        //the key is the mail service name, this is the default one, which does not extend any configuration
        'default' => [
            //message configuration
            'message_options' => [
                //from email address of the email
                'from' => '',
                //from name to be displayed instead of from address
                'from_name' => '',
                //reply-to email address of the email
                'reply_to' => '',
                //replyTo name to be displayed instead of the address
                'reply_to_name' => '',
                //destination email address as string or a list of email addresses
                'to' => [],
                //copy destination addresses
                'cc' => [],
                //hidden copy destination addresses
                'bcc' => [],
                //email subject
                'subject' => '',
                //body options - content can be plain text, HTML
                'body' => [
                    'content' => '',
                    'charset' => 'utf-8',
                ],
                //attachments config
                'attachments' => [
                    'files' => [],
                    'dir'   => [
                        'iterate'   => false,
                        'path'      => 'data/mail/attachments',
                        'recursive' => false,
                    ],
                ],
            ],
            /**
             * the mail transport to use can be any class implementing
             * Symfony\Component\Mailer\Transport\TransportInterface
             *
             * for standard mail transports, you can use these aliases:
             * - sendmail  => Symfony\Component\Mailer\Transport\SendmailTransport
             * - esmtp     => Symfony\Component\Mailer\Transport\Smtp\EsmtpTransport
             *
             * defaults to sendmail
             **/
            'transport' => 'sendmail',
            //options that will be used only if esmtp adapter is used
            'smtp_options' => [
                //hostname or IP address of the mail server
                'host' => '',
                //port of the mail server - 587 or 465 for secure connections
                'port'              => 587,
                'connection_config' => [
                    //the smtp authentication identity
                    'username' => '',
                    //the smtp authentication credential
                    'password' => '',
                    //to disable auto_tls set tls key to false
                    //it's not recommended to disable TLS while connecting to an SMTP server
                    'tls' => null,
                ],
            ],
        ],
        // option to log the SENT emails
        'log' => [
            'sent' => getcwd() . '/log/mail/sent.log',
        ],
    ],
];
