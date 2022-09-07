<?php
ini_set('max_execution_time', -1);
require_once __DIR__ . '/Utils.php';
class App
{
    public $url;
    public $host;
    public $path;
    public $name;
    public $fname;
    public $domain;
    public $absDomain;
    private $i = 1;
    private $first = true;

    public $css = [];

    /**
     * Jquery script
     */
    protected $JQS = '<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>';


    /**
     * List of css that are not to be scanned for if they contain images
     */
    public $node_css = ['bootstrap.css', 'tailwind.css', 'bulma.css'];

    public $icons = [
        'assets/css/ionicons.min.css',
    ];

    public $links = [];
    private $html_links = [];
    private $css_links = [];
    private $icon_links = [];
    private $processed_css = [];
    private $sub_css = [];

    public function __construct($url)
    {
        if (!$url) die('no url found');
    }

    protected function formatUrl($url)
    {

        if (!str_starts_with($url, 'http'))  $url = 'https://' .  trim($url, '/');
        // die($url);
        return $url;
    }

    /**
     * This scra[es html and proceed to its process
     */
    public function scrapeMain($url)
    {
        $url = $this->formatUrl($url);
        $this->url = $url;

        $this->main_path();

        if (!$str = @file_get_contents($url, false, $this->context())) return;

        // print_r($str);
        // die;

        $str = $this->replaceNodeModules($str);


        file_put_contents($this->url_filename($url), $str);

        // $str = file_get_contents($this->url_filename($url));

        $this->addCssIm($str, $this->path_name($url), 1);

        return $this->assets($str, $this->path_name($url), $url);
    }



    /**
     * This function scrapes a given `$url` 
     * and saves it in a path relative to the temps folder (generated) with respect to to the `$url`  passed
     * 
     * @param string $url A valid url fo a file name on the internet
     * @return boolean|int;
     */
    public function scrape($url)
    {
        $dir = $this->url_filename($url);
        if (file_exists($dir)) return true; // "File $url has been scraped";

        $str = @file_get_contents($url, false, $this->context());
        if (!$str)  return false;

        return   file_put_contents($dir, $str);
    }


    protected function replaceNodeModules($str)
    {
        $m = str_replace('http:', 'https:', $str);
        $str = str_replace(['"//', "'//"], ['"https://', "'https://"], $m);
        $m = str_replace($this->absDomain, '', $m);
        return $m;
    }

    public function main_path()
    {
        $u = $this->url;
        $parse = parse_url($u);
        $host = $parse['host'] ?? 'host';



        $path = $parse['path'] ?? '';
        // echo $path . '<br>';
        // dj($path);

        if (!str_contains($path, '.')) {
            $path = trim($path, '/') . '/index.html';
        }

        $dir_array = explode('/', $path);
        $name = array_pop($dir_array);
        if (is_null($name)) $name = 'index.html';

        $this->fname = $name;

        $dir = trim(implode('/', $dir_array), '/');

        $this->path = $dir;
        $this->domain = "https://$host/$dir";
        // dj($this->domain);

        // echo "$this->domain +++ >> <b style='color:red'>$u</b> <br>";

        $this->absDomain = "https://$host";
        $this->absPath =  dirname(__DIR__) . "/temps/$host";

        $this->absPath = trim(str_replace('\\', '/', $this->absPath), '/');


        $dir =  dirname(__DIR__) . "/temps/$host/" . $dir;

        $dir = trim(str_replace('\\', '/', $dir), '/');

        $this->host = $dir;

        if (!file_exists($dir)) {
            // echo "<b>$dir,----$u</b><br>";
            mkdir($dir, 0777, true);
        }
    }

    private function path_name($url)
    {
        $file = $this->url_filename($url);

        $path = str_replace($this->host, '', $file);

        return trim($path, '/');
    }


    public function assets($str, $path)
    {

        preg_match_all('/(src|href)="([\w\-.\/]+)"/U', $str, $src1);
        preg_match_all("/(src|href)='([\w\-.\/]+)'/U", $str, $src2);
        preg_match_all('/(src|href)=([\w\-.\/]+)[^\w\-.\/]/U', $str, $src3);

        // start with double quote and have version control
        preg_match_all('/(src|href)="([\w\-.\/]+)[^\w\-.\/]/U', $str, $src4);

        // start with double single and have version control
        preg_match_all("/(src|href)='([\w\-.\/]+)[^\w\-.\/]/U", $str, $src5);


        if ($this->first) $this->themeSwitch($str);

        $src = [...$src1[2], ...$src2[2], ...$src3[2], ...$src4[2], ...$src5[2]];

        $src = array_unique($src);

        // dj(__LINE__, $src);


        $links = array_filter($src, function ($u) {
            // htmls templates
            $a = !str_ends_with($u, '.html');
            $end = substr($u, strrpos($u, '/'));
            $b = str_contains($end, '.');

            return $a && $b;
        });

        // dj($links);

        $links = [...$this->links,  ...$links];

        foreach ($links as $k => $l) $links[$k] = $this->relative_path($l);
        $this->links = [...$links];

        // dj(__LINE__, $this->links);

        $this->parseCss($path);
        // dj(__LINE__);
        $this->ParseIcons($path);

        $htmls = array_filter($src, function ($u) {
            // return str_ends_with($u, '.html');

            $a = str_ends_with($u, '.html');
            $end = substr($u, strrpos($u, '/'));
            $b = !str_contains($end, '.');

            return $a || $b;
        });

        // dj(__LINE__, $htmls);
        $new_htmls = [];
        foreach ($htmls as $k => $l) {
            $h = $this->relative_path($l);
            if (!$h)  continue;
            $new_htmls[] = $h;
        }

        $this->html_links = [...$this->html_links, ...$new_htmls];

        // dj(__LINE__, $path, $this->html_links);


        // dj($this->domain, $this->links, $this->html_links);
        foreach ($this->links as $l) {
            if (file_exists($this->url_filename($l))) continue;
            $this->scrape($l);
        }


        $this->links = [];

        // sub css has being fetched now process for fonts, another sub css and images
        $this->parseSubCss($path);



        $this->html_links = [...array_unique($this->html_links)];
        // dj($this->domain, $this->host, $this->html_links);

        foreach ($this->html_links as $k => $u) {

            unset($this->html_links[$k]);

            // echo "$this->domain ++++ <b style='color:blue;'> $path </b>++ <b style='color:red;'>$old_url</b> ++ <b> $l</b> <br><br>";
            // continue;

            if (file_exists($this->url_filename($u))) continue;

            $this->scrapeMain($u);
        }

        die("<b style='color:green'>Files saved successfully inside $this->absPath </b>");

        $UT = new Utils;
        $zipname = str_replace('https://', '', $this->absDomain) . '-' . $this->path;
        $path = $this->host;

        $path =  dirname(__DIR__) . "/temps/$path";

        $zipname = preg_replace('/[^a-z0-9.-]/i', '_', $zipname);

        // dj($zipname);

        $UT->zip($path, "$zipname");
    }

    private function relative_path($m)
    {
        // return $m;
        $m =  strtok($m, '?');
        if (str_starts_with($m, '#')) return false;
        if (str_contains(strtolower($m), 'data:image')) return false;
        if (str_starts_with(strtolower($m), 'javascript:') || $m == 'javascript') return false;
        if (str_starts_with($m, '//')) return false;
        if (str_starts_with($m, 'mailto')) return false;
        if (str_starts_with($m, 'tel')) return false;
        if (trim($m, '/') == 'https') return false;

        if (str_starts_with($m, 'http')) $m = str_replace('http://', 'https://', $m);

        if (str_starts_with($m, 'https:') && !str_starts_with($m, $this->absDomain)) return false;

        if (str_starts_with($m, $this->absDomain))  return $this->abs_url(str_replace('/./', '/', $m));

        if (str_starts_with($m, '/')) return $this->abs_url(str_replace('/./', '/', ($m == '/' ? $m : $this->absDomain . $m)));


        if (str_starts_with($m, './'))   return $this->abs_url(str_replace('/./', '/',  "$this->absDomain/$m"));
        return $this->abs_url(str_replace('/./', '/', $this->domain . '/' . $m));
    }

    private function abs_url($url)
    {
        $url = trim(str_replace($this->absDomain, '', $url));

        $array = explode('/', $url);
        // $domain = array_shift($array);

        $parents = array();
        foreach ($array as $dir) {
            if (!$dir) continue;
            switch ($dir) {
                case '.':
                    // Don't need to do anything here
                    break;
                case '..':
                    array_pop($parents);
                    break;
                default:
                    $parents[] = $dir;
                    break;
            }
        }

        return  $this->absDomain .  '/' . implode('/', $parents);
    }

    /**
     * The directory to store a file from a url
     * @param string $url
     * @return string filename -The file name for the corresponding url
     */
    private function url_filename($url)
    {
        $parse = parse_url($url);
        $host = $parse['host'] ?? 'host';

        $path = $parse['path'] ?? '';

        $end = substr($path, strrpos($path, '/'));

        if (!str_contains($end, '.')) $path = trim($path, '/') . '/index.html';

        $path = trim($path, '/');

        $dir_array = explode('/', $path);

        $dir = trim(implode('/', $dir_array), '/');

        $dir =  dirname(__DIR__) . "/temps/$host/" . $dir;

        $dir = trim(str_replace('\\', '/', $dir), '/');

        // make folder to store the file
        $folder = substr($dir, 0, strrpos($dir, '/'));

        // dj(__LINE__, $folder);

        if (!file_exists($folder)) {
            // dj(__LINE__, $url, $this->url, $this->links, $this->url, $this->html_links);
            // echo "<b style='color:red'>$url</b>, <b style='color:blue;'>folder=$folder </b><br>";
            if (!mkdir($folder, 0777, true)) {
                dj(__LINE__, $url, $this->url, $this->links, $this->url, $this->html_links);
            }
        }

        return $dir;
    }

    /**
     * Only run on the first html scrape
     */
    private function themeSwitch($str)
    {
        if (!$this->first) return false;
        preg_match_all('/button value="(theme.*?)"/', $str, $themes);

        // dj($themes[0]);

        foreach ($themes[0] as $h) {
            preg_match('/value="(.*?)"/sU', $h, $m);
            // dj($m);
            $m = $m[1] ?? '#';
            if (str_starts_with($m, '#')) continue;

            if (str_starts_with($m, 'javascript:')) continue;

            $this->links[] = "assets/color/$m.css";
        }
        $this->first = false;

        // dj($this->links);
    }

    public function parseCss()
    {

        $this->css =  array_filter($this->links, function ($x) {
            $a = str_ends_with($x, '.css');
            $b = !str_contains($x, 'bootstrap');

            return $a && $b;
        });
        // dj($this->css);

        foreach ($this->css as $url) {
            if (in_array($url, $this->processed_css)) continue;

            $css = @file_get_contents($this->url_filename($url));
            if (!$css) continue;

            $this->addCssIm($css, $this->path_name($url));
        }
        // dj(__LINE__, $this->links);
    }

    /**
     * Extract background-images with url()
     */
    public function addCssIm($str, $path, $html = false)
    {
        preg_match_all('/background(-image)?:(\s*)url\(["\']?\s*([.\w\/\?\-]+)\s*["\']?\)/U', $str, $bc);
        $bc = $bc[3];

        $_path = str_ireplace($this->absPath, '', $path);

        $parts = explode('/', $_path);
        $current =  array_pop($parts);


        $path_dir = '/' . trim(implode('/', $parts), '/');

        $links = [];


        foreach ($bc as $m) {

            if (!str_starts_with($m, '/') && !str_starts_with($m, 'http'))   $m = $path_dir . '/' . trim($m, '/');

            $m = $this->relative_path($m);
            if (!$m) continue;

            if (!in_array($m, $this->links)) $links[] = $m;
        }

        // if (!empty($links)) dj(__LINE__, $this->absPath, $_path, $path_dir, $this->host, $path, $links);

        array_push($this->links, ...array_unique($links));
    }

    public function cssColors()
    {
        $all_colors = [];
        foreach ($this->css as $path) {
            $path = trim($path, '/');

            $dir = $this->host . '/' . $path;

            $css = @file_get_contents($dir);
            if (!$css) continue;

            preg_match_all('/color:(\s)*(.+);/', $css, $fc);
            $fc = $fc[0];
            preg_match_all('/background:(\s)*linear-gradient\(.+\);/', $css, $bi);
            $bi = $bi[0];

            $dir_array = explode('/', $path);
            $name = array_pop($dir_array);
            $name = str_replace('.css', '', $name);
            $path = implode('/', $dir_array);

            $bis = [];
            $colors = [];

            foreach ($fc as $h) {
                preg_match('/:\s*(([^\s]+);)/sU', $h, $m);
                // dj($m);
                $c = $m[1] ?? '#ffffff';

                $c = $this->hex3_hex6($c);

                if (!in_array($c, $colors)) $colors[] = $c;
            }

            $var_colors = '';
            foreach ($colors as $k => $v) {
                if (!str_ends_with($v, ';')) $v = "$v;";
                $var_colors .= '$color_' . "$k:$v";
            }

            file_put_contents($this->host . '/' . "$path/$name-var_colors.scss", $var_colors);

            // dj($colors);

            foreach ($bi as $h) {
                preg_match('/\(.+\);/sU', $h, $m);
                if (!empty($m[0]) && !in_array($m[0], $bis)) $bis[] = $m[0];
            }

            $var_colors = '';
            foreach ($bis as $k => $v) {
                if (!str_ends_with($v, ':')) $v = "$v;";
                $var_colors .= '$bi_' . "$k:$v";
            }
            file_put_contents($this->host . '/' . "$path/$name-var_bi.scss", $var_colors);
        }
    }

    public function hex3_hex6($color)
    {
        // convert 3-digit hex to 6-digits.
        if (!str_starts_with($color, '#')) return $color;

        $color = substr($color, 1);

        $c = str_split($color);
        if (count($c) === 3) {
            $color = $c;
            $color =  $color[0] . $color[0] . $color[1] . $color[1] . $color[2] . $color[2];
        }

        return  "#$color";
    }


    public function ParseIcons($abPath = "")
    {

        $this->icons = $this->css;

        // dj(__LINE__, $this->icons);

        foreach ($this->icons as $url) {

            if (in_array($url, $this->processed_css)) continue;
            $path = $this->path_name($url);


            $css = @file_get_contents($this->url_filename($url));

            if (!$css) continue;



            $this->processed_css = [...$this->processed_css, $path];

            preg_match_all('/url\(["\']([^"\':]+)["\']\)/', $css, $bc1);
            preg_match_all('/url\(([^"\'\(\)]+)\)/', $css, $bc2);
            // @import "../bootstrap/css/bootstrap.min.css"
            preg_match_all('/@import\s+[\'"]([^"\':]+)[\'"];/', $css, $bc3);



            $bc = [...$bc1[1], ...$bc2[1], ...$bc3[1]];

            $_path = str_replace($this->absPath, '', $path);
            $parts = explode('/', $_path);
            $current =  array_pop($parts);

            $path_dir = '/' . implode('/', $parts);
            $links = [];


            foreach ($bc as $m) {

                if (!str_starts_with($m, '/') && !str_starts_with($m, 'http'))   $m = '/' . trim($path_dir . '/' . trim($m, '/'), '/');

                $m = $this->relative_path($m);
                if (!$m) continue;

                if (str_ends_with($m, '.css'))   $this->sub_css[] = $m;

                if (!in_array($m, $this->links)) $this->links[] = $m;
                $links[] = $m;
            }
        }
    }

    public function parseSubCss()
    {

        // dj(__LINE__, $this->sub_css);
        $sub_css = [];


        foreach ($this->sub_css as  $url) {

            if (in_array($url, $this->processed_css)) continue;
            $path = $this->path_name($url);

            // $_path = str_ireplace($this->absPath, '', $path);

            // $parts = explode('/', $_path);

            $css = @file_get_contents($this->url_filename($url));

            if (!$css) continue;

            $this->processed_css = [...$this->processed_css, $url];


            preg_match_all('/url\(["\']([^"\']+)["\']\)/', $css, $bc1);
            preg_match_all('/url\(([^"\'\(\)]+)\)/', $css, $bc2);

            // @import "../bootstrap/css/bootstrap.min.css"
            preg_match_all('/@import\s+[\'"]([^"\']+)[\'"];/', $css, $bc3);

            // dj(__LINE__, $bc3);

            $bc = [...$bc1[1], ...$bc2[1], ...$bc3[1]];


            $_path = str_ireplace($this->absPath, '', $path);
            $parts = explode('/', $_path);
            $current = array_pop($parts);
            // $parts =  array_reverse($parts);

            $path_dir = implode('/', $parts);


            foreach ($bc as $m) {

                if (!str_starts_with($m, '/') && !str_starts_with($m, 'http')) $m = '/' . trim($path_dir . '/' . trim($m, '/'), '/');

                $m = $this->relative_path($m);
                if (!$m) continue;

                if (str_ends_with($m, '.css'))   $sub_css[] = $m;

                if (!file_exists($this->url_filename($m)))  $this->scrape($m);
            }
        }


        $this->sub_css = $sub_css;
        // scrape the sub css again
        if (!empty($sub_css)) $this->parseSubCss();
    }

    public function context()
    {
        $context = stream_context_create(
            [
                "http" => [
                    "header" => "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
                ]
            ]
        );

        return $context;
    }


    private function repDot($path, $link)
    {
        $m = $link;
        $m_1 = explode('/', $m);

        // $dir_array = explode('/', $path);
        // $name = array_pop($dir_array);

        $parts = explode('/', $path);
        array_pop($parts);
        // dj($m_1);

        foreach ($m_1 as $k => $m2) {
            if ($m_1[$k] == '..') {
                $m_1[$k] = $parts[$k] ?? '..';
            }
        }


        $m = trim(implode('/', $m_1), '/');

        return $m;
    }


    private function mkPath($path, $isFile = false)
    {
        if ($isFile) {
            $a = explode('/', $path);
            array_pop($a);
            $path = implode('/', $a);
        }

        if (!file_exists($path)) mkdir($path, 0777, true);
    }
}


function dj(...$x)
{
    if (count($x) < 2)  $x = $x[0] ?? '';
    if (gettype($x) != 'object' && gettype($x) != 'array') $x = [$x];
    die(json_encode($x));
}
