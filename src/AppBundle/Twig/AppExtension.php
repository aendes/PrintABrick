<?php

namespace AppBundle\Twig;

use AppBundle\Transformer\FormatTransformer;

class AppExtension extends \Twig_Extension
{
    /** @var FormatTransformer */
    private $formatTransformer;

    /**
     * AppExtension constructor.
     *
     * @param FormatTransformer $formatTransformer
     */
    public function __construct($formatTransformer)
    {
        $this->formatTransformer = $formatTransformer;
    }

    public function getFilters()
    {
        return [
            new \Twig_SimpleFilter('bytesToSize', [$this, 'bytesToSize']),
        ];
    }

    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('remoteSize', [$this, 'remoteSize']),
            new \Twig_SimpleFunction('remoteFilename', [$this, 'remoteFilename']),
            new \Twig_SimpleFunction('remoteFileExists', [$this, 'remoteFileExists']),
            new \Twig_SimpleFunction('fileTimestamp', [$this, 'fileTimestamp']),
        ];
    }

    public function bytesToSize($bytes, $precision = 2)
    {
        return $this->formatTransformer->bytesToSize($bytes, $precision);
    }

    /**
     * @param string $url
     *
     * @return bool
     */
    public function remoteFileExists($url)
    {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $status === 200 ? true : false;
    }

    public function remoteSize($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);

        $data = curl_exec($ch);
        $size = curl_getinfo($ch, CURLINFO_CONTENT_LENGTH_DOWNLOAD);

        curl_close($ch);

        return $size;
    }

    public function fileTimestamp($filePath)
    {
        $changeDate = filemtime($_SERVER['DOCUMENT_ROOT'].'/'.$filePath);
        if (!$changeDate) {
            //Fallback if mtime could not be found:
            $changeDate = mktime(0, 0, 0, date('m'), date('d'), date('Y'));
        }

        return $filePath.'?'.$changeDate;
    }

    public function remoteFilename($url)
    {
        return basename($url);
    }
}
