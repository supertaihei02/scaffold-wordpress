<?php
/* *******************************
 *              引数
 * 第一引数に 1 or 0 の値を取る
 * 0: wordmoveによる移行時に node_modulesとvendor以下を同期しない
 * 1: wordmoveによる移行時に node_modulesとvendor以下を同期する
 * *******************************/
$move_library = false;
if (isset($argv[1]) && intval($argv[1]) === 1) {
    $move_library = true;
}

$web_root = dirname(dirname(__DIR__)) . '/html';
require $web_root. '/vendor/autoload.php';

class MovefileGenerator
{
    static function createEngine()
    {
        $env_dir = dirname(dirname(__DIR__));
        $dotenv = new Dotenv\Dotenv($env_dir);
        $dotenv->load();

        $template_dir = __DIR__ . '/template';
        $loader = new \Twig_Loader_Filesystem($template_dir);
        $twig = new \Twig_Environment(
            $loader, array('debug' => true)
        );
        $twig->addExtension(new \Twig_Extension_Debug());
        return $twig;
    }    
}

$twig = MovefileGenerator::createEngine();
$template = $twig->loadTemplate("Movefile.twig");
$content = $template->render(array(
    'env' =>  $_ENV,
    'move_library' => $move_library
));
$output = $web_root . '/Movefile';
file_put_contents($output, $content);

echo $content . PHP_EOL;
echo 'Generated: ' . $output . PHP_EOL;
