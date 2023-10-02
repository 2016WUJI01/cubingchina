<?php
$this->widget('GridView', [
  'dataProvider'=>new CArrayDataProvider($winners, [
    'pagination'=>false,
    'sort'=>false,
  ]),
  'itemsCssClass'=>'table table-condensed table-hover table-boxed',
  'columns'=>[
    [
      'name'=>Yii::t('common', 'Event'),
      'type'=>'raw',
      'value'=>'CHtml::link(Events::getFullEventNameWithIcon($data->eventId), [
        "/results/c",
        "id"=>$data->competitionId,
        "type"=>"all",
        "#"=>$data->eventId,
      ])',
    ],
    [
      'name'=>Yii::t('Results', 'Person'),
      'type'=>'raw',
      'value'=>'Persons::getLinkByNameNId($data->personName, $data->personId)',
    ],
    [
      'name'=>Yii::t('common', 'Best'),
      'type'=>'raw',
      'value'=>'$data->getTime("best", false, true)',
      'headerHtmlOptions'=>['class'=>'result'],
      'htmlOptions'=>['class'=>'result'],
    ],
    [
      'name'=>Yii::t('common', 'Average'),
      'type'=>'raw',
      'value'=>'$data->getTime("average", false, true)',
      'headerHtmlOptions'=>['class'=>'result'],
      'htmlOptions'=>['class'=>'result'],
    ],
    [
      'name'=>Yii::t('common', 'Region'),
      'value'=>'Region::getIconName($data->personCountry->name, $data->personCountry->iso2)',
      'type'=>'raw',
      'htmlOptions'=>['class'=>'region'],
    ],
    [
      'name'=>Yii::t('common', 'Detail'),
      'type'=>'raw',
      'value'=>'$data->detail',
    ],
  ],
]); ?>
