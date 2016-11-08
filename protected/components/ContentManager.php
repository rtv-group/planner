<?php

class ContentManager extends CApplicationComponent
{
    private $eol = YII_DEBUG ? '<br>' : PHP_EOL;

    public function GetBGContentArr($pointId, $pointChannel, $pointDatetimeStr, $weekDay)
    {
        $playlistsType = 0;
        if ($pointChannel === 3) {
            $playlistsType = 2;
        }

        $connection = Yii::app()->db;

        $pointDate = new DateTime($pointDatetimeStr);
        $pointDateStr = date_format ( $pointDate, "Y-m-d" );

        $sql = "SELECT `t3`.`id`, `files`, `type`, `fromDatetime`, `toDatetime`, `fromTime`, `toTime`, `playlistId`, `t4`.`id` AS 'id-author' FROM `channel` AS `t1` " .
            "JOIN `playlist_to_channel` AS `t2` " .
            "JOIN `playlists` AS `t3` " .
            "JOIN `user` AS `t4` " .
            "ON `t1`.`id` = `t2`.`channelId` " .
            "AND `t3`.`author` = `t4`.`username` " .
            "AND `t2`.`playlistId` = `t3`.`id` " .
            "AND `t1`.`id_point` = '" . $pointId . "' " .
            "AND `t1`.`internalId` = '" . $pointChannel . "' " .
            "AND `t3`.`fromDatetime` <= '" . $pointDatetimeStr . "' " .
            "AND `t3`.`toDatetime` >= '" . $pointDatetimeStr . "' " .
            "AND `t3`.`" . $weekDay . "` = '1' " .
            "AND (`t3`.`type` = '" . $playlistsType . "')" .
            "ORDER BY `fromTime`;";

        $command=$connection->createCommand($sql);
        $rows=$command->queryAll();

        $blocksArr = array ();
        foreach ($rows as $row) {
            $fromDatetime = date_create ( $row['fromDatetime'] );
            $toDatetime = date_create ( $row['toDatetime'] );

            $fromTime = $row['fromTime'];
            $toTime = $row['toTime'];

            $files = $row['files'];
            $type = $row['type'];
            $playlistId = $row['id'];
            $authorId = $row['id-author'];

            /* if today starts showing check broadcasting is later showing begin */
            if (($fromDatetime < $toDatetime) && ($fromTime < $toTime)) {
                if (((date_format ( $fromDatetime, "Y-m-d" ) != $pointDateStr)
                    && (date_format ( $toDatetime, "Y-m-d" ) != $pointDateStr))
                    || ((date_format ( $fromDatetime, "Y-m-d" ) == $pointDateStr)
                    && (strtotime ( date_format ( $fromDatetime, "h:i:s" ) ) < strtotime ( $fromTime )))
                    || ((date_format ( $toDatetime, "Y-m-d" ) == $pointDateStr)
                    && (strtotime ( date_format ( $toDatetime, "h:i:s" ) ) > strtotime ( $toTime )))
                ) {
                    $blocksArr [] = array (
                        'from' => $fromTime,
                        'to' => $toTime,
                        'fromDateTime' => new DateTime ( $fromTime ),
                        'toDateTime' => new DateTime ( $toTime ),
                        'files' => $files,
                        'type' => $type,
                        'playlistId' => $playlistId,
                        'authorId' => $authorId
                    );
                }
            }
        }

        foreach ( $blocksArr as &$block ) {
            if($block ['type'] == 2) {
                $sql = "SELECT `url` FROM `stream` WHERE `playlist_id` = '" . $block['playlistId'] . "';";

                $command=$connection->createCommand($sql);
                $rows=$command->queryAll();

                if($rows) {
                    $duration = $block ['toDateTime']->getTimestamp() - $block ['fromDateTime']->getTimestamp();;
                    $block ["filesWithDuration"] = array ();
                    foreach ($rows as $row) {
                        $block ["filesWithDuration"] [] = array (
                                $duration + 5, //5 seconds above just not to have mute between turns
                                $row['url'],
                                $duration . " " . $row['url'] . " "
                                    . "pl:" . $block['playlistId'] . ";"
                                    . "author:" . $block['authorId'] . ""
                                    . $this->eol /*ready to output str*/
                        );
                    }

                    $block ["duration"] = $duration;
                }
            } else {
                $files = implode ( "','", explode ( ",", $block ['files'] ) );
                $from = $block ['from'];

                $sql = "SELECT `duration`, `name` ".
                        "FROM `file` WHERE `id` IN ('" . $files . "') ".
                        "ORDER BY FIELD(`id`,'".$files."');";

                $command=$connection->createCommand($sql);
                $rows=$command->queryAll();

                $duration = 0;
                $block ["filesWithDuration"] = array ();
                foreach($rows as $row) {
                    $duration += $row['duration'];
                    $block ["filesWithDuration"] [] = array (
                        $row['duration'],
                        $row['name'],
                        $row['duration'] . " "
                            . $row['name'] . " "
                            . "pl:" . $block['playlistId'] . ";"
                            . "author:" . $block['authorId'] . " "
                            . "" . $this->eol /*ready to output str*/
                    );
                }

                $block ["duration"] = $duration;
            }
        }

        return $blocksArr;
    }

    public function GetAdvContentArr($pointId, $pointChannel, $pointDatetimeStr, $weekDay)
    {
        $connection = Yii::app()->db;

        $sql = "SELECT `files`, `fromDatetime`, `toDatetime`, `fromTime`, `toTime`, `every`, `playlistId`, `t4`.`id` AS 'id-author' FROM `channel` AS `t1` " .
            "JOIN `playlist_to_channel` AS `t2` " .
            "JOIN `playlists` AS `t3` " .
            "JOIN `user` AS `t4` " .
            "ON `t1`.`id` = `t2`.`channelId` " .
            "AND `t3`.`author` = `t4`.`username` " .
            "AND `t2`.`playlistId` = `t3`.`id` " .
            "AND `t1`.`id_point` = '" . $pointId . "' " .
            "AND `t1`.`internalId` = '" . $pointChannel . "' " .
            "AND `t3`.`fromDatetime` <= '" . $pointDatetimeStr . "' " .
            "AND `t3`.`toDatetime` >= '" . $pointDatetimeStr . "' " .
            "AND `t3`.`" . $weekDay . "` = '1' " . "AND `t3`.`type` = '1';";

        $command=$connection->createCommand($sql);
        $rows=$command->queryAll();

        $advArr = array ();
        foreach ($rows as $row) {
            $fromDatetime = date_create ( $row['fromDatetime']);
            $toDatetime = date_create ( $row['toDatetime']);

            $every = $row['every'];
            $files = $row['files'];
            $files = implode ( "','", explode ( ",", $files ) );

            $sql = "SELECT `duration`, `name` ".
                "FROM `file` WHERE `id` IN ('" . $files . "') ".
                "ORDER BY FIELD(`id`,'".$files."');";

            $command=$connection->createCommand($sql);
            $rows2=$command->queryAll();

            $duration = 0;
            $filesWithDuration = array ();
            foreach ($rows2 as $row2) {
                $duration += $row2 ['duration'];
                $filesWithDuration [] = array (
                    $row2['duration'],
                    $row2['name'],
                    $row2['duration'] . " " . $row2['name'] . " "
                        . "pl:" . $row['playlistId'] . ";"
                        . "author:" . $row['id-author'] . ""
                        . $this->eol /*ready to output str*/
                );
            }

            $duration = intval ( $duration );

            $repeating = explode ( ":", $every );
            $repeating = $repeating [0] * 60 * 60 + $repeating [1] * 60 + $repeating [2];

            $startTime = new DateTime ($row['fromTime']);
            $endTime = new DateTime ($row['toTime']);

            $curTime = clone $startTime;

            while ( $curTime < $endTime ) {
                $endBlockTime = clone $curTime;
                $endBlockTime->add ( new DateInterval ( 'PT' . $duration . 'S' ) );

                $fromTime = clone $curTime;
                $fromTime = $fromTime->format ( 'H:i:s' );

                $toTime  = clone $endBlockTime;
                $toTime = $toTime->format ( 'H:i:s' );

                $advArr [] = array (
                    'from' => $fromTime,
                    'to' => $toTime,
                    'fromDateTime' => clone $curTime,
                    'toDateTime' => clone $endBlockTime,
                    'files' => $files,
                    'duration' => $duration,
                    "filesWithDuration" => $filesWithDuration
                );

                $curTime->add ( new DateInterval ( 'PT' . $repeating . 'S' ) );
            }
        }

        $size = count($advArr) - 1;
        for ($ii = $size; $ii >= 0; $ii--) {
            for ($jj = 0; $jj <= ($ii-1); $jj++) {
                $first = new DateTime ($advArr[$jj]['from']);
                $next = new DateTime ($advArr[$jj+1]['from']);

                if ($first > $next) {
                        $tmp = $advArr[$jj];
                        $advArr[$jj] = $advArr[$jj+1];
                        $advArr[$jj+1] = $tmp;
                }
            }
        }

        return $advArr;
    }

    public function GenerateContentBlock($block, $from = null)
    {
        $blockStr = '';

        if (!isset($block["from"]) || !isset($block["filesWithDuration"])) {
            return $blockStr;
        }

        if ($from === null) {
            $from = $block["from"];
        }

        $blockStr .= $from . $this->eol;
        foreach ($block['filesWithDuration'] as $files) {
            if (isset($files[2])) {
                $blockStr .= $files[2];
            }
        }

        return $blockStr . $this->eol;
    }

    public function PrepareSpoolPath($pathAppendix)
    {
        $pathAppendix = explode("/", $pathAppendix);

        $contentPath = $_SERVER["DOCUMENT_ROOT"];

        foreach($pathAppendix as $folder)
        {
            $contentPath .= "/" . $folder;
            if (!file_exists($contentPath) && !is_dir($contentPath))
            {
                mkdir($contentPath);
            }
        }

        $contentPath .= "/";
        return $contentPath;
    }

    private function arrayInsert(&$array,$element,$position=null)
    {
        if (count($array) == 0) {
            $array[] = $element;
        }
        elseif (is_numeric($position) && $position < 0) {
            if((count($array)+$position) < 0) {
                $array = $this->arrayInsert($array,$element,0);
            }
            else {
                $array[count($array)+$position] = $element;
            }
        }
        elseif (is_numeric($position) && isset($array[$position])) {
            $part1 = array_slice($array,0,$position,true);
            $part2 = array_slice($array,$position,null,true);
            $array = array_merge($part1,array($position=>$element),$part2);
            foreach($array as $key=>$item) {
                if (is_null($item)) {
                    unset($array[$key]);
                }
            }
        }
        elseif (is_null($position)) {
            $array[] = $element;
        }
        elseif (!isset($array[$position])) {
            $array[$position] = $element;
        }
        $array = array_merge($array);
        return $array;
    }
}
