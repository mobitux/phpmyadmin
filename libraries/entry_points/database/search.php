<?php
/**
 * searches the entire database
 *
 * @todo    make use of UNION when searching multiple tables
 * @todo    display executed query, optional?
 * @package PhpMyAdmin
 */
declare(strict_types=1);

use PhpMyAdmin\Database\Search;
use PhpMyAdmin\DatabaseInterface;
use PhpMyAdmin\Html\Generator;
use PhpMyAdmin\Response;
use PhpMyAdmin\Template;
use PhpMyAdmin\Url;
use PhpMyAdmin\Util;

if (! defined('PHPMYADMIN')) {
    exit;
}

global $containerBuilder, $db, $err_url, $url_query, $url_params, $tables, $num_tables, $total_num_tables, $sub_part;
global $is_show_stats, $db_is_system_schema, $tooltip_truename, $tooltip_aliasname, $pos;

/** @var Response $response */
$response = $containerBuilder->get(Response::class);

/** @var DatabaseInterface $dbi */
$dbi = $containerBuilder->get(DatabaseInterface::class);

/** @var Template $template */
$template = $containerBuilder->get('template');

$header = $response->getHeader();
$scripts = $header->getScripts();
$scripts->addFile('database/search.js');
$scripts->addFile('sql.js');
$scripts->addFile('makegrid.js');

require ROOT_PATH . 'libraries/db_common.inc.php';

// If config variable $GLOBALS['cfg']['UseDbSearch'] is on false : exit.
if (! $GLOBALS['cfg']['UseDbSearch']) {
    Generator::mysqlDie(
        __('Access denied!'),
        '',
        false,
        $err_url
    );
}
$url_params['goto'] = Url::getFromRoute('/database/search');
$url_query .= Url::getCommon($url_params, '&');

// Create a database search instance
$db_search = new Search($dbi, $db, $template);

// Display top links if we are not in an Ajax request
if (! $response->isAjax()) {
    [
        $tables,
        $num_tables,
        $total_num_tables,
        $sub_part,
        $is_show_stats,
        $db_is_system_schema,
        $tooltip_truename,
        $tooltip_aliasname,
        $pos,
    ] = Util::getDbInfo($db, isset($sub_part) ? $sub_part : '');
}

// Main search form has been submitted, get results
if (isset($_POST['submit_search'])) {
    $response->addHTML($db_search->getSearchResults());
}

// If we are in an Ajax request, we need to exit after displaying all the HTML
if ($response->isAjax() && empty($_REQUEST['ajax_page_request'])) {
    exit;
}

// Display the search form
$response->addHTML($db_search->getMainHtml());
