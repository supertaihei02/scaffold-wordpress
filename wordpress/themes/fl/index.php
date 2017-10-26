<?php
/**
 * @var $si_twig \Twig_Environment
 */
global $si_twig, $si_logger;

$current_page = CustomizerTwig::currentPage();
get_header();
$si_twig->display($current_page[0], $current_page[1]);
