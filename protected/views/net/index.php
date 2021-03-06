<?php
/* @var $this NetController */
/* @var $dataProvider CActiveDataProvider */

Yii::app()->clientScript->registerScript('search', "
$('.search-button').click(function(){
    $('.search-form').slideToggle();
    return false;
});
$('.search-form form').submit(function(){
    $('#statistic-grid').yiiGridView('update', {
        data: $(this).serialize()
    });
    return false;
});
");

$this->menu = array(
  array('label'=>'Create', 'url'=>array('create')),
);
?>

<h1>Nets</h1>

<div class='row row-menu'>
    <span class="btn btn-default search-button">
        <span class="glyphicon glyphicon-search"></span>
        Filter
    </span>

    <div class="search-form" style="display:none">
      <?php $this->renderPartial('_search',array(
          'model' => $model,
      )); ?>
    </div><!-- search-form -->
</div>

<div class="network-list container-fluid">
    <?php $this->widget('zii.widgets.CListView', array(
      'dataProvider' => $model->search(),
      'itemView' => '_view',
      'pager' => [
          'firstPageLabel'=>'&laquo;',
          'prevPageLabel'=>'&lsaquo;',
          'nextPageLabel'=>'&rsaquo;',
          'lastPageLabel'=>'&raquo;',
          'maxButtonCount'=>'5',
          'cssFile'=>Yii::app()->getBaseUrl(true).'/css/pager.css'
      ],
    )); ?>
</div>
