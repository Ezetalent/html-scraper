
<?php
class Utils
{
    public function CURL($url, $options = [])
    {
        $userAgent = "Mozilla/5.0 (Windows; U; Windows NT 5.1;en; rv:1.9.1.6) Gecko/20091201 Firefox/3.5.6";

        $ch = curl_init();
        $ckfile = tempnam(__DIR__ . '/cookies/', "CURLCOOKIE");


        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => $this->BrowserHeaders(),
            CURLOPT_USERAGENT => $userAgent,
            CURLOPT_REFERER => 'http://www.google.com',
            CURLOPT_ENCODING => 'gzip,deflate,br',
            CURLOPT_AUTOREFERER =>   TRUE,

            // array
            CURLOPT_COOKIEJAR => $ckfile,
            ...$options
        ]);

        $response = curl_exec($ch);
        $error = curl_error($ch);
        curl_close($ch);

        // dj($ckfile);

        // print_r($error);
        // die;

        if ($error)   return $error;
        return $response;
    }

    protected function BrowserHeaders()
    {
        $header[] = "User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64; rv:67.0) Gecko/20100101 Firefox/67.0";
        $header[] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $header[] = "Accept-Language: en-US,en;q=0.5";
        $header[] = "Accept-Encoding: gzip, deflate, br";
        $header[] = "Connection: keep-alive";
        $header[] = "Upgrade-Insecure-Requests: 1";
        $header[] = "Cache-Control: max-age=0";
        $header[] = "TE: Trailers"; // browsers keep this blank.
        $header[] = 'Accept: */*';
        $header[] = 'Accept-Encoding: gzip, deflate';
        $header[] = "Referer: 'http://www.google.com";
        return $header;
    }

    function zip($source, $destination)
    {

        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }

        $destination = preg_replace('/[^a-z0-9]/i', '-', $destination);

        $zipname = "$destination.zip";
        if (!file_exists(dirname(__DIR__) . '/zips')) mkdir(dirname(__DIR__) . '/zips', 0777, true);

        $destination = dirname(__DIR__) . "/zips/$destination";

        if (file_exists("$destination.zip")) {
            unlink("$destination.zip");
            // $destination = $destination . '_' . date('Y_m_d_H_i_s');
        }

        $destination = "$destination.zip";

        $zip = new ZipArchive();
        if (!$zip->open($destination, ZIPARCHIVE::CREATE)) {
            die('unable to create the zip file');
        }

        $source = str_replace('\\', '/', realpath($source));


        if (is_dir($source) === true) {

            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);

            foreach ($files as $file) {
                $file = str_replace('\\', '/', realpath($file));

                // Ignore "." and ".." folders
                if (in_array(substr($file, strrpos($file, '/') + 1), array('.', '..'))) continue;


                $dir = str_replace($source . '/', '', $file . '/');
                $parentPath = dirname($source);

                $dir = str_replace($parentPath . '/', '', $dir);

                if (is_dir($file) === true && $dir) {
                    $zip->addEmptyDir($dir);
                } else if (is_file($file) === true) {

                    $filename  = str_replace($source . '/', '', $file);
                    $zip->addFile($file, $filename);
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString(basename($source), file_get_contents($source));
        }


        $zip->close();
        dj('done');

        // ob_end_clean();
        // header("Content-type: application/zip");
        // header("Content-Disposition: attachment; filename=$zipname");
        // header("Pragma: no-cache");
        // header("Expires: 0");
        // readfile("$destination");
    }
}
