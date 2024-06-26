<div class="col-lg-12 results-person" data-person-id="<?php echo $person->id; ?>">
  <h1 class="text-center"><?php echo $user && $user->id === Yii::app()->user->id ? CHtml::link($person->name, array('/user/profile')) : $person->name; ?></h1>
  <?php if ($user && $user->avatar): ?>
  <div class="text-center"><?php echo $user->avatar->img; ?></div>
  <?php endif ?>
  <div class="panel panel-info person-detail">
    <div class="panel-body">
      <div class="row">
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('Results', 'Name'); ?>:</span>
          <span class="info-value"><?php echo $person->name; ?></span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('common', 'Region'); ?>:</span>
          <span class="info-value">
            <?php echo Yii::t('Region', $person->country->name); ?>
          </span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('Results', 'Competitions'); ?>:</span>
          <span class="info-value"><?php echo count($competitions); ?></span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('common', 'WCA ID'); ?>:</span>
          <span class="info-value"><?php echo Persons::getWCAIconLinkByNameNId($person->name, $person->id); ?></span>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <?php if ($person->gender && $person->gender !== 'o'): ?>
          <span class="info-title"><?php echo Yii::t('common', 'Gender'); ?>:</span>
          <span class="info-value"><?php echo strtolower($person->gender) == 'f' ? Yii::t('common', 'Female') : Yii::t('common', 'Male'); ?></span>
          <?php endif; ?>
        </div>
        <div class="col-md-4 col-sm-6 col-xs-12 mt-10">
          <span class="info-title"><?php echo Yii::t('Results', 'Career'); ?>:</span>
          <span class="info-value"><?php echo sprintf('%d.%02d.%02d - %d.%02d.%02d', $firstCompetition->year, $firstCompetition->month, $firstCompetition->day, $lastCompetition->year, $lastCompetition->endMonth, $lastCompetition->endDay); ?></span>
        </div>
      </div>
    </div>
  </div>
  <?php echo CHtml::link(Html::fontAwesome('object-group') . Yii::t('summary', '{year} Annual Summary', [
    '{year}'=>$year,
  ]), [
    '/summary/person',
    'year'=>$year,
    'id'=>$person->id
  ], [
    'class'=>'btn btn-lg btn-theme',
  ]); ?>
  <h2><?php echo Yii::t('Results', 'Current Personal Records') . Persons::getBattleCheckBox($person->name, $person->id, 'span', array('class'=>'small')); ?></h2>
  <?php
  $this->widget('GridView', array(
    'dataProvider'=>new CArrayDataProvider(array_values($personRanks), array(
      'pagination'=>false,
      'sort'=>false,
    )),
    'front'=>true,
    'template'=>'{items}',
    'columns'=>array(
      array(
        'name'=>Yii::t('common', 'Event'),
        'type'=>'raw',
        'value'=>'CHtml::link(Events::getFullEventNameWithIcon($data->eventId), "#" . $data->event->id)',
      ),
      array(
        'name'=>Yii::t('statistics', 'NR'),
        'type'=>'raw',
        'value'=>'$data->getRank("countryRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('statistics', 'CR'),
        'type'=>'raw',
        'value'=>'$data->getRank("continentRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('statistics', 'WR'),
        'type'=>'raw',
        'value'=>'$data->getRank("worldRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('common', 'Single'),
        'type'=>'raw',
        'value'=>'CHtml::link(Results::formatTime($data->best, $data->eventId), array(
          "/results/rankings",
          "event"=>$data->eventId,
          "region"=>$data->person->countryId,
        ))',
        // 'headerHtmlOptions'=>array('class'=>'best'),
      ),
      array(
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'$data->average("best")',
        // 'headerHtmlOptions'=>array('class'=>'best'),
      ),
      array(
        'name'=>Yii::t('statistics', 'WR'),
        'type'=>'raw',
        'value'=>'$data->average("worldRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('statistics', 'CR'),
        'type'=>'raw',
        'value'=>'$data->average("continentRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'name'=>Yii::t('statistics', 'NR'),
        'type'=>'raw',
        'value'=>'$data->average("countryRank")',
        'headerHtmlOptions'=>array('class'=>'record'),
      ),
      array(
        'header'=>Yii::t('statistics', 'Gold'),
        'value'=>'$data->medals["gold"] ?: ""',
      ),
      array(
        'header'=>Yii::t('statistics', 'Silver'),
        'value'=>'$data->medals["silver"] ?: ""',
      ),
      array(
        'header'=>Yii::t('statistics', 'Bronze'),
        'value'=>'$data->medals["bronze"] ?: ""',
      ),
      array(
        'header'=>Yii::t('statistics', 'Solves/Attempts'),
        'value'=>'$data->medals["solve"] . "/" . $data->medals["attempt"]',
      ),
    ),
  )); ?>
  <h2><?php echo Yii::t('statistics', 'Sum of Ranks'); ?></h2>
  <?php
  $this->widget('GridView', array(
    'dataProvider'=>new CArrayDataProvider($sumOfRanks, array(
      'pagination'=>false,
      'sort'=>false,
    )),
    'front'=>true,
    'template'=>'{items}',
    'columns'=>array(
      array(
        'header'=>'',
        'value'=>'Yii::t("common", ucfirst($data->type))',
      ),
      array(
        'name'=>Yii::t('statistics', 'Sum of NR'),
        'type'=>'raw',
        'value'=>'CHtml::link($data->countryRank, array(
          "/results/statistics",
          "name"=>"sum-of-ranks",
          "type"=>$data->type,
          "region"=>"' . $person->countryId . '",
        ))',
      ),
      array(
        'name'=>Yii::t('statistics', 'NR'),
        'type'=>'raw',
        'value'=>'$data->getRank("NR")',
      ),
      array(
        'name'=>Yii::t('statistics', 'Sum of CR'),
        'type'=>'raw',
        'value'=>'CHtml::link($data->continentRank, array(
          "/results/statistics",
          "name"=>"sum-of-ranks",
          "type"=>$data->type,
          "region"=>"' . $person->country->continentId . '",
        ))',
      ),
      array(
        'name'=>Yii::t('statistics', 'CR'),
        'type'=>'raw',
        'value'=>'$data->getRank("CR")',
      ),
      array(
        'name'=>Yii::t('statistics', 'Sum of WR'),
        'type'=>'raw',
        'value'=>'CHtml::link($data->worldRank, array(
          "/results/statistics",
          "name"=>"sum-of-ranks",
          "type"=>$data->type,
          "region"=>"World",
        ))',
      ),
      array(
        'name'=>Yii::t('statistics', 'WR'),
        'type'=>'raw',
        'value'=>'$data->getRank("WR")',
      ),
    ),
  )); ?>
  <?php foreach ($podiums as $name=>$data): ?>
  <h2><?php echo Yii::t('Results', '{region} Championship Podiums', [
    '{region}'=>Championships::getRegionName($name, $person),
  ]); ?></h2>
  <?php
  $this->widget('GroupGridView', array(
    'dataProvider'=>new CArrayDataProvider($data, array(
      'pagination'=>false,
      'sort'=>false,
    )),
    'itemsCssClass'=>'table table-condensed table-hover table-boxed',
    'groupKey'=>'competition.year',
    'groupHeader'=>'$data->competitionLink',
    'columns'=>array(
      array(
        'name'=>Yii::t('common', 'Event'),
        'type'=>'raw',
        'value'=>'Events::getFullEventNameWithIcon($data->eventId)',
      ),
      array(
        'name'=>Yii::t('Results', 'Place'),
        'type'=>'raw',
        'value'=>'$data->pos',
        'headerHtmlOptions'=>array('class'=>'place'),
      ),
      array(
        'name'=>Yii::t('common', 'Single'),
        'type'=>'raw',
        'value'=>'$data->getTime("best", false, true)',
      ),
      array(
        'name'=>Yii::t('common', 'Average'),
        'type'=>'raw',
        'value'=>'$data->getTime("average", false, true)',
      ),
      array(
        'name'=>Yii::t('common', 'Detail'),
        'type'=>'raw',
        'value'=>'$data->detail',
      ),
    ),
  )); ?>
  <?php endforeach; ?>
  <?php if (array_sum($overAll) > 0): ?>
  <div class="row">
    <?php $overAllDataProvider = new CArrayDataProvider(array($overAll), array(
      'pagination'=>false,
      'sort'=>false,
    )); ?>
    <?php if ($overAll['gold'] + $overAll['silver'] + $overAll['bronze'] > 0): ?>
    <div class="col-sm-6 col-xs-12">
      <h2><?php echo Yii::t('Results', 'Overall Medal Collection'); ?></h2>
      <?php
      $this->widget('GridView', array(
        'dataProvider'=>$overAllDataProvider,
        'front'=>true,
        'template'=>'{items}',
        'columns'=>array(
          array(
            'header'=>Yii::t('statistics', 'Gold'),
            'value'=>'$data["gold"] ?: ""',
          ),
          array(
            'header'=>Yii::t('statistics', 'Silver'),
            'value'=>'$data["silver"] ?: ""',
          ),
          array(
            'header'=>Yii::t('statistics', 'Bronze'),
            'value'=>'$data["bronze"] ?: ""',
          ),
        ),
      )); ?>
    </div>
    <?php endif; ?>
    <?php if ($overAll['WR'] + $overAll['CR'] + $overAll['NR'] > 0): ?>
    <div class="col-sm-6 col-xs-12">
      <h2><?php echo Yii::t('Results', 'Overall Record Collection'); ?></h2>
      <?php
      $this->widget('GridView', array(
        'dataProvider'=>$overAllDataProvider,
        'front'=>true,
        'template'=>'{items}',
        'columns'=>array(
          array(
            'name'=>Yii::t('Results', 'WR'),
            'type'=>'raw',
            'value'=>'$data["WR"] ?: ""',
          ),
          array(
            'name'=>Yii::t('Results', 'CR'),
            'type'=>'raw',
            'value'=>'$data["CR"] ?: ""',
          ),
          array(
            'name'=>Yii::t('Results', 'NR'),
            'type'=>'raw',
            'value'=>'$data["NR"] ?: ""',
          ),
        ),
      )); ?>
    </div>
    <?php endif; ?>
  </div>
  <?php endif; ?>
  <?php $hasRecords = !empty($historyWR) || !empty($historyCR) || !empty($historyNR); ?>
  <ul class="nav nav-tabs">
    <li class="active"><a href="#history" data-toggle="tab"><?php echo Yii::t('common', 'Results'); ?></a></li>
    <?php if ($hasRecords): ?>
    <li><a href="#records" data-toggle="tab"><?php echo Yii::t('common', 'Records'); ?></a></li>
    <?php endif; ?>
    <li><a href="#person-map" data-toggle="tab"><?php echo Yii::t('Persons', 'Map'); ?></a></li>
    <li><a href="#competition-history" data-toggle="tab"><?php echo Yii::t('common', 'Competitions'); ?></a></li>
    <?php if (count($competitions) > 1): ?>
    <li><a href="#misc" data-toggle="tab"><?php echo Yii::t('common', 'Misc'); ?></a></li>
    <?php endif; ?>
  </ul>
  <div class="tab-content">
    <div class="tab-pane active" id="history">
      <ul class="nav nav-tabs">
        <li class="active"><a href="#by-event" data-toggle="tab"><?php echo Yii::t('common', 'By Event'); ?></a></li>
        <li><a href="#by-competition" data-toggle="tab"><?php echo Yii::t('common', 'By Competition'); ?></a></li>
      </ul>
      <div class="tab-content">
        <div class="tab-pane active" id="by-event">
          <?php
          $this->widget('GroupRankGridView', array(
            'dataProvider'=>new CArrayDataProvider($byEvent, array(
              'pagination'=>false,
              'sort'=>false,
            )),
            'itemsCssClass'=>'table table-condensed table-hover table-boxed',
            'groupKey'=>'eventId',
            'groupHeader'=>'CHtml::openTag("a", array(
                "name"=>$data->eventId,
              )) . "</a>" . Events::getFullEventNameWithIcon($data->eventId)',
            'rankKey'=>'competitionId',
            'repeatHeader'=>true,
            'columns'=>array(
              array(
                'class'=>'RankColumn',
                'name'=>Yii::t('Results', 'Competition'),
                'type'=>'raw',
                'value'=>'$displayRank ? $data->competitionLink : ""',
                'headerHtmlOptions'=>array('class'=>'competition_name'),
              ),
              array(
                'name'=>Yii::t('common', 'Round'),
                'type'=>'raw',
                'value'=>'Yii::t("RoundTypes", $data->round->cellName)',
                'headerHtmlOptions'=>array('class'=>'round'),
              ),
              array(
                'name'=>Yii::t('Results', 'Place'),
                'type'=>'raw',
                'value'=>'$data->pos',
                'headerHtmlOptions'=>array('class'=>'place'),
              ),
              array(
                'name'=>Yii::t('common', 'Best'),
                'type'=>'raw',
                'value'=>'$data->getTime("best", true, true)',
                'headerHtmlOptions'=>array('class'=>'result'),
                'htmlOptions'=>array('class'=>'result'),
              ),
              array(
                'name'=>Yii::t('common', 'Average'),
                'type'=>'raw',
                'value'=>'$data->getTime("average", true, true)',
                'headerHtmlOptions'=>array('class'=>'result'),
                'htmlOptions'=>array('class'=>'result'),
              ),
              array(
                'name'=>Yii::t('common', 'Detail'),
                'type'=>'raw',
                'value'=>'$data->detail',
              ),
            ),
          )); ?>
        </div>
        <div class="tab-pane" id="by-competition">
          <?php
          $this->widget('GroupRankGridView', array(
            'dataProvider'=>new CArrayDataProvider($byCompetition, array(
              'pagination'=>false,
              'sort'=>false,
            )),
            'itemsCssClass'=>'table table-condensed table-hover table-boxed',
            'groupKey'=>'competitionId',
            'groupHeader'=>'$data->competitionLink',
            'rankKey'=>'eventId',
            'repeatHeader'=>true,
            'columns'=>array(
              array(
                'class'=>'RankColumn',
                'name'=>Yii::t('common', 'Event'),
                'type'=>'raw',
                'value'=>'$displayRank ? CHtml::link(Events::getFullEventNameWithIcon($data->eventId), array(
                  "/results/c",
                  "id"=>$data->competitionId,
                  "type"=>"all",
                  "#"=>$data->eventId,
                )) : ""',
              ),
              array(
                'name'=>Yii::t('common', 'Round'),
                'type'=>'raw',
                'value'=>'Yii::t("RoundTypes", $data->round->cellName)',
                'headerHtmlOptions'=>array('class'=>'round'),
              ),
              array(
                'name'=>Yii::t('Results', 'Place'),
                'type'=>'raw',
                'value'=>'$data->pos',
                'headerHtmlOptions'=>array('class'=>'place'),
              ),
              array(
                'name'=>Yii::t('common', 'Best'),
                'type'=>'raw',
                'value'=>'$data->getTime("best", true, true)',
                'headerHtmlOptions'=>array('class'=>'result'),
                'htmlOptions'=>array('class'=>'result'),
              ),
              array(
                'name'=>Yii::t('common', 'Average'),
                'type'=>'raw',
                'value'=>'$data->getTime("average", true, true)',
                'headerHtmlOptions'=>array('class'=>'result'),
                'htmlOptions'=>array('class'=>'result'),
              ),
              array(
                'name'=>Yii::t('common', 'Detail'),
                'type'=>'raw',
                'value'=>'$data->detail',
              ),
            ),
          )); ?>
        </div>
      </div>
    </div>
    <?php if ($hasRecords): ?>
    <div class="tab-pane" id="records">
      <?php if (!empty($historyWR)): ?>
      <h2><?php echo Yii::t('Results', 'History of World Records'); ?></h2>
      <?php
      $this->widget('GroupGridView', array(
        'dataProvider'=>new CArrayDataProvider($historyWR, array(
          'pagination'=>false,
          'sort'=>false,
        )),
        'itemsCssClass'=>'table table-condensed table-hover table-boxed',
        'groupKey'=>'eventId',
        'groupHeader'=>'Events::getFullEventNameWithIcon($data->eventId)',
        'columns'=>array(
          array(
            'name'=>Yii::t('common', 'Event'),
            'type'=>'raw',
            'value'=>'',
          ),
          array(
            'name'=>Yii::t('common', 'Single'),
            'type'=>'raw',
            'value'=>'$data->regionalSingleRecord == "WR" ? $data->getTime("best", false, true) : ""',
          ),
          array(
            'name'=>Yii::t('common', 'Average'),
            'type'=>'raw',
            'value'=>'$data->regionalAverageRecord == "WR" ? $data->getTime("average", false, true): ""',
          ),
          array(
            'name'=>Yii::t('Results', 'Competition'),
            'type'=>'raw',
            'value'=>'$data->competitionLink',
            'headerHtmlOptions'=>array('class'=>'competition_name'),
          ),
          array(
            'name'=>Yii::t('common', 'Round'),
            'type'=>'raw',
            'value'=>'Yii::t("RoundTypes", $data->round->cellName)',
            'headerHtmlOptions'=>array('class'=>'round'),
          ),
          array(
            'name'=>Yii::t('common', 'Detail'),
            'type'=>'raw',
            'value'=>'$data->getDetail(true)',
          ),
        ),
      )); ?>
      <?php endif; ?>
      <?php if (!empty($historyCR)): ?>
      <h2><?php echo Yii::t('Results', 'History of Continental Records'); ?></h2>
      <?php
      $this->widget('GroupGridView', array(
        'dataProvider'=>new CArrayDataProvider($historyCR, array(
          'pagination'=>false,
          'sort'=>false,
        )),
        'itemsCssClass'=>'table table-condensed table-hover table-boxed',
        'groupKey'=>'eventId',
        'groupHeader'=>'Events::getFullEventNameWithIcon($data->eventId)',
        'columns'=>array(
          array(
            'name'=>Yii::t('common', 'Event'),
            'type'=>'raw',
            'value'=>'',
          ),
          array(
            'name'=>Yii::t('common', 'Single'),
            'type'=>'raw',
            'value'=>'!in_array($data->regionalSingleRecord, array("WR", "NR", "")) ? $data->getTime("best", false, true) : ""',
          ),
          array(
            'name'=>Yii::t('common', 'Average'),
            'type'=>'raw',
            'value'=>'!in_array($data->regionalAverageRecord, array("WR", "NR", "")) ? $data->getTime("average", false, true): ""',
          ),
          array(
            'name'=>Yii::t('Results', 'Competition'),
            'type'=>'raw',
            'value'=>'$data->competitionLink',
            'headerHtmlOptions'=>array('class'=>'competition_name'),
          ),
          array(
            'name'=>Yii::t('common', 'Round'),
            'type'=>'raw',
            'value'=>'Yii::t("RoundTypes", $data->round->cellName)',
            'headerHtmlOptions'=>array('class'=>'round'),
          ),
          array(
            'name'=>Yii::t('common', 'Detail'),
            'type'=>'raw',
            'value'=>'$data->getDetail(true)',
          ),
        ),
      )); ?>
      <?php endif; ?>
      <?php if (!empty($historyNR)): ?>
      <h2><?php echo Yii::t('Results', 'History of National Records'); ?></h2>
      <?php
      $this->widget('GroupGridView', array(
        'dataProvider'=>new CArrayDataProvider($historyNR, array(
          'pagination'=>false,
          'sort'=>false,
        )),
        'itemsCssClass'=>'table table-condensed table-hover table-boxed',
        'groupKey'=>'eventId',
        'groupHeader'=>'Events::getFullEventNameWithIcon($data->eventId)',
        'columns'=>array(
          array(
            'name'=>Yii::t('common', 'Event'),
            'type'=>'raw',
            'value'=>'',
          ),
          array(
            'name'=>Yii::t('common', 'Single'),
            'type'=>'raw',
            'value'=>'$data->regionalSingleRecord == "NR" ? $data->getTime("best", false, true) : ""',
          ),
          array(
            'name'=>Yii::t('common', 'Average'),
            'type'=>'raw',
            'value'=>'$data->regionalAverageRecord == "NR" ? $data->getTime("average", false, true): ""',
          ),
          array(
            'name'=>Yii::t('Results', 'Competition'),
            'type'=>'raw',
            'value'=>'$data->competitionLink',
            'headerHtmlOptions'=>array('class'=>'competition_name'),
          ),
          array(
            'name'=>Yii::t('common', 'Round'),
            'type'=>'raw',
            'value'=>'Yii::t("RoundTypes", $data->round->cellName)',
            'headerHtmlOptions'=>array('class'=>'round'),
          ),
          array(
            'name'=>Yii::t('common', 'Detail'),
            'type'=>'raw',
            'value'=>'$data->getDetail(true)',
          ),
        ),
      )); ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>
    <div class="tab-pane" id="person-map">
      <?php if (count($visitedProvinces) > 0): ?>
      <div id="competition-provinces"></div>
      <?php endif; ?>
      <div id="competition-cluster"></div>
    </div>
    <div class="tab-pane" id="competition-history">
      <?php
      $this->widget('GridView', array(
        'dataProvider'=>new NonSortArrayDataProvider($competitions, array(
          'pagination'=>false,
        )),
        'template'=>'{items}{pager}',
        'enableSorting'=>false,
        'front'=>true,
        // 'rowCssClassExpression'=>'$data->isInProgress() ? "success" : ($data->isEnded() ? "active" : "info")',
        'columns'=>array(
          array(
            'header'=>Yii::t('Registration', 'No.'),
            'type'=>'raw',
            'value'=>'$data->number',
          ),
          array(
            'name'=>'date',
            'header'=>Yii::t('Competition', 'Date'),
            'type'=>'raw',
            'value'=>'$data->getDate()',
          ),
          array(
            'name'=>'name',
            'header'=>Yii::t('Competition', 'Name'),
            'type'=>'raw',
            'value'=>'$data->getCompetitionLink()',
          ),
          array(
            'name'=>'countryId',
            'header'=>Yii::t('common', 'Region'),
            'type'=>'raw',
            'value'=>'$data->country ? Region::getIconName($data->country->name, $data->country->iso2) : $data->countryId',
            'htmlOptions'=>array('class'=>'region'),
          ),
          array(
            'name'=>'cityName',
            'header'=>Yii::t('common', 'City'),
            'type'=>'raw',
            'value'=>'$data->getCityInfo()',
          ),
        ),
      )); ?>
    </div>
    <?php if (count($competitions) > 1 || $organizedCompetitions != []): ?>
    <div class="tab-pane" id="misc">
      <div class="row">
        <?php if (count($closestCubers) > 1): ?>
        <div class="col-md-4">
          <h2><?php echo Yii::t('Results', 'Closest Cubers'); ?></h2>
          <?php
          $this->widget('GridView', array(
            'dataProvider'=>new CArrayDataProvider($closestCubers, array(
              'pagination'=>false,
              'sort'=>false,
            )),
            'front'=>true,
            'template'=>'{items}',
            'columns'=>array(
              array(
                'name'=>Yii::t('Results', 'Person'),
                'type'=>'raw',
                'value'=>'Persons::getLinkByNameNId($data["personName"], $data["personId"])',
              ),
              array(
                'name'=>'count',
                'header'=>Yii::t('Results', 'Competitions'),
              ),
            ),
          )); ?>
        </div>
        <?php endif; ?>
        <?php if (count($seenCubers) > 1): ?>
        <div class="col-md-4">
          <h2><?php echo Yii::t('Results', 'Seen Cubers'); ?></h2>
          <?php
          $this->widget('GridView', array(
            'dataProvider'=>new CArrayDataProvider($seenCubers, array(
              'pagination'=>false,
              'sort'=>false,
            )),
            'front'=>true,
            'template'=>'{items}',
            'columns'=>array(
              array(
                'name'=>'count',
                'header'=>Yii::t('Results', 'Times'),
              ),
              array(
                'name'=>'competitors',
                'header'=>Yii::t('Results', 'Competitors'),
              ),
            ),
          )); ?>
        </div>
        <?php endif; ?>
        <?php if (count($visitedProvinces) > 0): ?>
        <div class="col-md-4">
          <h2><?php echo Yii::t('Results', 'Visited Provinces'); ?></h2>
          <?php
          $this->widget('GridView', array(
            'dataProvider'=>new CArrayDataProvider($visitedProvinces, array(
              'pagination'=>false,
              'sort'=>false,
            )),
            'front'=>true,
            'template'=>'{items}',
            'columns'=>array(
              array(
                'name'=>'count',
                'header'=>Yii::t('Results', 'Times'),
              ),
              array(
                'header'=>Yii::t('common', 'Province'),
                'value'=>'Yii::t("Region", ActiveRecord::getModelAttributeValue($data, "name"))',
              ),
            ),
          )); ?>
        </div>
        <?php endif; ?>
      </div>
      <?php if ($organizedCompetitions !== []): ?>
      <h2><?php echo Yii::t('common', 'Organized Competitions'); ?></h2>
      <?php
      $this->widget('GridView', array(
        'dataProvider'=>new CArrayDataProvider($organizedCompetitions, array(
          'pagination'=>false,
          'sort'=>false,
        )),
        'front'=>true,
        'template'=>'{items}',
        'columns'=>array(
          array(
            'header'=>Yii::t('Competition', 'Date'),
            'name'=>'date',
            'type'=>'raw',
            'value'=>'$data->getDisplayDate()',
          ),
          array(
            'header'=>Yii::t('Competition', 'Name'),
            'name'=>'name',
            'type'=>'raw',
            'value'=>'$data->getCompetitionLink()',
          ),
          array(
            'header'=>Yii::t('Competition', 'Province'),
            'name'=>'province_id',
            'type'=>'raw',
            'value'=>'$data->getLocationInfo("province")',
          ),
          array(
            'header'=>Yii::t('Competition', 'City'),
            'name'=>'city_id',
            'type'=>'raw',
            'value'=>'$data->getLocationInfo("city")',
          ),
          array(
            'header'=>Yii::t('Competition', 'Venue'),
            'name'=>'venue',
            'type'=>'raw',
            'value'=>'$data->getLocationInfo("venue")',
          ),
        ),
      )); ?>
      <?php endif; ?>
    </div>
    <?php endif; ?>
  </div>
</div>
