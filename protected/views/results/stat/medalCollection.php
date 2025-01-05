<div class="col-lg-12 competition-wca">
  <p class="text-muted"><small><?php echo Yii::t('statistics', 'Generated on {time}.', array(
    '{time}'=>date('Y-m-d H:i:s', $time),
  )); ?></small></p>
  <?php $form = $this->beginWidget('ActiveForm', array(
    'htmlOptions'=>array(
      'role'=>'form',
      'class'=>'form',
    ),
    'method'=>'get',
    'action'=>array(
      '/results/statistics',
      'name'=>'medal-collection',
    ),
  )); ?>
    <div class="form-group row">
      <?php foreach (array_chunk(Events::getNormalTranslatedEvents(), 3, true) as $events): ?>
      <div class="col-md-4 col-xs-6">
        <div class="row">
        <?php foreach ($events as $eventId=>$name): ?>
          <div class="col-xs-4">
            <div class="checkbox">
              <label>
                <?php echo CHtml::checkBox('event[]', in_array("$eventId", $eventIds), array(
                  'value'=>$eventId,
                )); ?>
                <?php echo Events::getEventIcon($eventId); ?>
              </label>
            </div>
          </div>
        <?php endforeach; ?>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
    <div class="form-inline">
      <div class="form-group">
        <label for="region"><?php echo Yii::t('common', 'Region'); ?></label>
        <?php echo CHtml::dropDownList('region', $region, Region::getWCARegions(), array(
          'class'=>'form-control',
        )); ?>
      </div>
      <div class="form-group">
        <label for="Competition_year"><?php echo Yii::t('common', 'Gender'); ?></label>
        <?php echo CHtml::dropDownList('gender', $gender, Persons::getGenders(), array(
          'class'=>'form-control',
        )); ?>
      </div>
      <div class="form-group">
        <label for="year"><?php echo Yii::t('common', 'Year'); ?></label>
        <?php echo CHtml::dropDownList('year', $year, Competitions::getYears(false), array(
          'class'=>'form-control',
          'prompt'=>Yii::t('common', 'All'),
        )); ?>
      </div>
    </div>
    <button type="submit" class="btn btn-theme"><?php echo Yii::t('common', 'Submit'); ?></button>
  <?php $this->endWidget(); ?>
  <?php
  $this->widget('RankGridView', array(
    'dataProvider'=>new NonSortArrayDataProvider($statistic['rows'], array(
      'pagination'=>array(
        'pageSize'=>Statistics::$limit,
        'pageVar'=>'page',
      ),
      'sliceData'=>false,
      'totalItemCount'=>$statistic['count'],
    )),
    'template'=>'{items}{pager}',
    'enableSorting'=>false,
    'front'=>true,
    'rankKey'=>$statistic['rankKey'],
    'rank'=>$statistic['rank'],
    'count'=>($page - 1) * MedalCollection::$limit,
    'columns'=>array_map(function($column) {
      $column['header'] = Yii::app()->evaluateExpression($column['header']);
      return $column;
    }, $statistic['columns']),
  )); ?>
</div>
