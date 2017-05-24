<?php

class m170523_195940_file_to_playlist extends CDbMigration
{
  public function up()
  {
    $ii = 0;

    $fileToPlaylistTable = Yii::app()->db->schema->getTable('file');
    if (!isset($fileToPlaylistTable)) {
        $this->execute('ALTER TABLE `file` CHANGE `id` `id` INT NOT NULL AUTO_INCREMENT;');
        $ii++;
        echo $ii;
        echo PHP_EOL.PHP_EOL;
    }

    $fileToPlaylistTable = Yii::app()->db->schema->getTable('file_to_playlist');
    if (!isset($fileToPlaylistTable)) {
        $this->execute('CREATE TABLE `file_to_playlist` (
             `id` int(11) NOT NULL AUTO_INCREMENT,
             `id_file` int(11) NOT NULL,
             `id_playlist` int(11) NOT NULL,
             PRIMARY KEY (`id`),
             KEY `id_file` (`id_file`),
             KEY `id_playlist` (`id_playlist`),
             CONSTRAINT `file_fk` FOREIGN KEY (`id_file`) REFERENCES `file`(`id`) ON DELETE CASCADE ON UPDATE CASCADE,
             CONSTRAINT `playlist_fk` FOREIGN KEY (`id_playlist`) REFERENCES `playlists` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
           ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;');

        $ii++;
        echo $ii;
        echo PHP_EOL.PHP_EOL;
    }

    $rows = Yii::app()->db->createCommand()
        ->select('id, files')
        ->from('playlists')
        ->queryAll();

    $ii++;
    echo $ii;
    echo PHP_EOL.PHP_EOL;

    foreach ($rows as $row) {
        $playlistId =  intval($row['id']);
        $files = explode(',', $row['files']);

        foreach ($files as $fileId) {
            $fileId = intval($fileId);
            $fileInstance = Yii::app()->db->createCommand()
                ->select('id')
                ->from('file')
                ->where('id=:id', [':id'=>$fileId])
                ->queryAll();

            if (count($fileInstance) === 1) {
              Yii::app()->db->createCommand()
                ->insert('file_to_playlist', array(
                    'id_file'=>$fileId,
                    'id_playlist'=>$playlistId,
                ));
            }
        }
    }
  }

  public function down()
  {
    echo "m170523_195940_file_to_playlist does not support migration down.\n";
    return false;
  }
}
