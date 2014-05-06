<?php
/**
 * This is the template for generating the phpdocs of a specified model.
 *
 * @var ModelDocCode $this
 * @var CActiveRecord $model
 * @var ReflectionClass $reflection
 *
 *
 * @author Brett O'Donnell <cornernote@gmail.com>
 * @author Zain Ul abidin <zainengineer@gmail.com>
 * @copyright 2013 Mr PHP
 * @link https://github.com/cornernote/gii-modeldoc-generator
 * @license BSD-3-Clause https://raw.github.com/cornernote/gii-modeldoc-generator/master/LICENSE
 */
$properties = array(' *');

// get own methods and properties
$modelClass = $reflection->getShortName();
$selfMethods = CHtml::listData($reflection->getMethods(), 'name', 'name');
$selfProperties = CHtml::listData($reflection->getProperties(), 'name', 'name');

// table fields
$properties[] = ' * Table ' . $model->tableName();
foreach ($model->tableSchema->columns as $column) {
    $type = $column->type;
    if (($column->dbType == 'datetime') || ($column->dbType == 'date')) {
        $type = 'string'; // $column->dbType;
    }
    if (strpos($column->dbType, 'decimal') !== false) {
        $type = 'number';
    }
    if(!empty($column->comment)) {
        $comment = preg_replace('/[\r\n]+/u', ' ', $column->comment);
        $comment = ' ' . mb_substr($comment, 0, 100);
    } else {
        $comment = '';
    }
    $properties[] = ' * @property ' . $type . ' $' . $column->name . $comment;
}
$properties[] = ' *';

// relations
$relations = $model->relations();
if ($relations) {
    $properties[] = ' * Relations';
    foreach ($relations as $relationName => $relation) {
        if (in_array($relation[0], array('CBelongsToRelation', 'CHasOneRelation'))) {
            $relationClass = $relation[1][0] == '\\' ? $relation[1] : '\\' . $relation[1];
            $properties[] = ' * @property ' . $relationClass . ' $' . $relationName;
        } elseif (in_array($relation[0], array('CHasManyRelation', 'CManyManyRelation'))) {
            $relationClass = $relation[1][0] == '\\' ? $relation[1] : '\\' . $relation[1];
            $properties[] = ' * @property ' . $relationClass . '[] $' . $relationName;
        } elseif (in_array($relation[0], array('CStatRelation'))) {
            $properties[] = ' * @property integer $' . $relationName;
        } else {
            $properties[] = ' * @property unknown $' . $relationName;
        }
    }
    $properties[] = ' *';
}

// scopes
$scopes = $model->scopes();
if ($scopes) {
    $properties[] = ' * Scopes';
    foreach (array_keys($scopes) as $scopeName) {
        $properties[] = " * @method {$modelClass} {$scopeName}()";
    }
    $properties[] = ' *';
}

// active record
$properties[] = ' * @see \CActiveRecord';
if ($this->addModelMethodDoc)
    $properties[] = " * @method static \{$modelClass} model(string \$className = null)";
$properties[] = " * @method \{$modelClass} find(\$condition = '', array \$params = array())";
$properties[] = " * @method \{$modelClass} findByPk(\$pk, \$condition = '', array \$params = array())";
$properties[] = " * @method \{$modelClass} findByAttributes(array \$attributes, \$condition = '', array \$params = array())";
$properties[] = " * @method \{$modelClass} fndBySql(\$sql, array \$params = array())";
$properties[] = " * @method \{$modelClass}[] findAll(\$condition = '', array \$params = array())";
$properties[] = " * @method \{$modelClass}[] findAllByPk(\$pk, \$condition = '', array \$params = array())";
$properties[] = " * @method \{$modelClass}[] findAllByAttributes(array \$attributes, \$condition = '', array \$params = array())";
$properties[] = " * @method \{$modelClass}[] findAllBySql(\$sql, array \$params = array())";
$properties[] = " * @method \{$modelClass} with()";
$properties[] = " * @method \{$modelClass} together()";
$properties[] = " * @method \{$modelClass} cache(\$duration, \$dependency = null, \$queryCount = 1)";
$properties[] = " * @method \{$modelClass} resetScope(\$resetDefault = true)";
$properties[] = " * @method \{$modelClass} populateRecord(\$attributes, \$callAfterFind = true)";
$properties[] = " * @method \{$modelClass}[] populateRecords(\$data, \$callAfterFind = true, \$index = null)";

$properties[] = " *";

// behaviors
$behaviors = $model->behaviors();
if ($behaviors) {
    foreach ($behaviors as $behavior) {
        $properties[] = ' * @mixin ' . $this->getBehaviorClass($behavior);
    }
    $properties[] = ' *';
}

// output the contents
$content = $this->getContent($reflection->getFileName());
echo $content[0];
echo $this->beginBlock . "\n";
echo implode("\n", $properties) . "\n";
echo $this->endBlock;
echo $content[1];
