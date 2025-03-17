<?php

namespace Devrabiul\AdvancedFileManager\Traits;

trait FileManagerTranslateTrait
{
    public static function fileManagerTranslateSort($targetPath): void
    {
        $getMessagesArray = include($targetPath);
        ksort($getMessagesArray);
        $remainingMessagesFileContents = "<?php\n\nreturn [\n";
        foreach ($getMessagesArray as $newMsgKey => $newMsgValue) {
            $remainingMessagesFileContents .= "\t\"" . $newMsgKey . "\" => \"" . $newMsgValue . "\",\n";
        }
        $remainingMessagesFileContents .= "];\n";
        file_put_contents($targetPath, $remainingMessagesFileContents);
    }
}