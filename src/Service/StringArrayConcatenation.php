<?php

declare(strict_types=1);

namespace FriendsOfSylius\SyliusImportExportPlugin\Service;

class StringArrayConcatenation implements StringArrayConcatenationInterface
{
    /**
     * @param array $messageArray
     *
     * @return string
     */
    public function getConcatenatedString(array $messageArray): string
    {
        // atleast one string has to be delivered anyways, so directly put this one in so its easier to insert commas
        $messageReturn = sprintf(
            '%s',
            $messageArray[0]
        );

        // unset used array so its not in the string twice
        unset($messageArray[0]);

        // walk through the rest of the array and put them into the string and divide all the substrings with a comma
        foreach ($messageArray as $messageString) {
            $messageReturn = sprintf(
                '%s, %s',
                $messageReturn,
                $messageString
            );
        }

        return $messageReturn;
    }
}
