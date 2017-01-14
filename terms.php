<?php
/**
 * MIT License
 *
 * Copyright (c) 2005-2017 TorrentPier
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

define('BB_SCRIPT', 'terms');
define('BB_ROOT', './');
require_once __DIR__ . '/common.php';
require_once(INC_DIR . 'bbcode.php');

/** @var \TorrentPier\Di $di */
$di = \TorrentPier\Di::getInstance();

$user->session_start();

if (!$di->config->get('terms') && !IS_ADMIN) {
    redirect('index.php');
}

$content = $di->view->make('terms', [
    'isAdmin' => IS_ADMIN,
    'termsHtml' => bbcode2html($di->config->get('terms')),
    'transUrl' => make_url('admin/admin_terms.php'),
    'transUrlName' => $di->translator->trans('Control panel'),
]);

/** @var \Symfony\Component\HttpFoundation\Response $response */
$response = \Symfony\Component\HttpFoundation\Response::create();
$response->setContent($content);

$response->prepare($di->request);
$response->send();
