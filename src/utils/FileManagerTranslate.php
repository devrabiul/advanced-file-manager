<?php

use Devrabiul\AdvancedFileManager\Traits\FileManagerTranslateTrait;

if (!function_exists('fileManagerTrans')) {
    function fileManagerTrans($key = null): string|null
    {
        if (!empty($key) || $key == 0) {
            return fileManagerTranslateStore(local: app()->getLocale() ?? 'en' , key: $key);
        }
        return $key;
    }

    function fileManagerTranslateStore(string $local, string|null $key): array|string|null
    {
        try {
            $messagePath = __DIR__ . '/../../resources/lang/' . $local . '/messages.php';
            $translatedMessagesArray = include($messagePath);
            $processedKey = str_replace('_', ' ', fileManagerTranslateInvalidChar(str_replace("\'", "'", $key)));
            $key = fileManagerTranslateInvalidChar($key);

            if (!array_key_exists($key, $translatedMessagesArray)) {
                $translatedMessagesArray[$key] = $processedKey;
                $languageFileContents = "<?php\n\nreturn [\n";
                foreach ($translatedMessagesArray as $languageKey => $value) {
                    $languageFileContents .= "\t\"" . $languageKey . "\" => \"" . $value . "\",\n";
                }
                $languageFileContents .= "];\n";
                file_put_contents($messagePath, $languageFileContents);
                FileManagerTranslateTrait::fileManagerTranslateSort(targetPath: $messagePath);
                $message = $processedKey;
            } else {
                $message = $translatedMessagesArray[$key];
            }
        } catch (\Exception $exception) {
            $message = str_replace('_', ' ', fileManagerTranslateInvalidChar(str_replace("\'", "'", $key)));
        }
        return $message;
    }

    function fileManagerTranslateInvalidChar($str): array|string
    {
        return str_ireplace(['"', ';', '<', '>'], ' ', preg_replace('/\s\s+/', ' ', $str));
    }
}