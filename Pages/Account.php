<?php

    /**
     * Plugin administration
     */

    namespace IdnoPlugins\Mastodon\Pages {

        /**
         * Default class to serve the homepage
         */
        class Account extends \Idno\Common\Page
        {

            function getContent()
            {
                $this->gatekeeper(); // Logged-in users only
                /*if ($mastodon = \Idno\Core\site()->plugins()->get('Mastodon')) {
                    $oauth_url = $mastodon->getAuthURL();
                }*/
                $oauth_url = \Idno\Core\site()->config()->getDisplayURL() . 'mastodon/auth';
                $t = \Idno\Core\site()->template();
                $body = $t->__(array('oauth_url' => $oauth_url))->draw('account/mastodon');
                $t->__(array('title' => 'Mastodon', 'body' => $body))->drawPage();
            }

            function postContent() {
                $this->gatekeeper(); // Logged-in users only
                if (($this->getInput('remove'))) {
                    $user = \Idno\Core\site()->session()->currentUser();
                    $user->mastodon = array();
                    $user->save();
                    \Idno\Core\site()->session()->addMessage('Your Mastodon settings have been removed from your account.');
                }
                if ($this->getInput('login') && $this->getInput('username')) {
                    $user = \Idno\Core\site()->session()->currentUser();
                    $tmp = explode('@', $this->getInput('username'));
                    $login = $this->getInput('login');
                    $username = $this->getInput('username');
                    $server = $tmp[1];
                    $user->mastodon = array('server' => $server, 'login' => $login, 'username' => $username, 'bearer' => '');
                    if (empty(Idno::site()->config()->mastodon->servers[$server])){
                        $mastodon = \Idno\Core\site()->plugins()->get('Mastodon');
                        $mastodonApi = $mastodon->connect($server);
                        $appConfig = $mastodonApi->createApp($server);
                        $authUrl = $mastodonApi->getAuthUrl();
                        $knownuser = \Idno\Core\site()->session()->currentUser()->getHandle();
                        \Idno\Core\site()->config->config['mastodon'] = [
                    'servers' => [$server =>['name' => $server, 'user' => $knownuser, 'issued_ad' => now, 'client_id' => '', 'client_secret' => ''] ]
                ];
                    }
                }
                $this->forward(\Idno\Core\site()->config()->getDisplayURL() . 'account/mastodon/');
            }

        }

    }
