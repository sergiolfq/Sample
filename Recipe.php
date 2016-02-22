<?php

/**
 * This is the model class for table "recipe".
 *
 * The followings are the available columns in table 'recipe':
 * @property integer $id_rec recipe id
 * @property integer $id_his medical record id "history"
 * @property string $tratamiento  threatment
 * @property string $indicaciones "orders"
 * @property string $fecha_rec "date"
 * @property integer $id_u      "user id"
 *
 * The followings are the available model relations:
 * @property Historia $idHis
 * @propertly Usuario $idU 
 */
class Recipe extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'recipe';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
		//	array('i fecha_rec', 'required'),
			array('id_his', 'numerical', 'integerOnly'=>true),
			array('tratamiento, indicaciones','filter','filter'=>array($obj=new CHtmlPurifier(),'purify')),
			array('tratamiento, indicaciones', 'length', 'max'=>1000),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id_rec, id_his, tratamiento, indicaciones, fecha_rec', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'idHis' => array(self::BELONGS_TO, 'Historia', 'id_his'),
			'idU' => array(self::BELONGS_TO, 'Usuario', 'id_u'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id_rec' => 'Id Rec',
			'id_his' => 'Id His',
			'tratamiento' => 'Tratamiento',
			'indicaciones' => 'Indicaciones',
			'fecha_rec' => 'Fecha R&eacutecipe',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search($id=null)
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;
           	//$criteria->select = 't.* ';

                $criteria->join='LEFT JOIN historia  ON  historia.id_his = t.id_his';   
	//	$criteria->compare('id_rec',$this->id_rec);
		$criteria->condition='t.id_his= :value ';
		$criteria->params=array(':value'=>yii::app()->user->ultimaHistoria);
	/*	$criteria->compare('tratamiento',$this->tratamiento,true);
		$criteria->compare('indicaciones',$this->indicaciones,true);
		$criteria->compare('fecha_rec',$this->fecha_rec,true);*/
	if($id!=null)
		$criteria->addCondition("id_rec!=".$id);
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
/** provee la informacion suficiente para realizar el recipe Medico 
*
*
*/
	public function informacion(){
        
         $infoMedico='';
         $infoPaciente='';
         $infoCentro='';	         
	 $webroot = Yii::getPathOfAlias('webroot');
         $documento=simplexml_load_file($webroot."/images/documento.xml");;
         $infoCentro=utf8_decode($documento->toy->titulo);
         $medico=Usuario::model()->findByPk($this->id_u);
         $historia=Historia::model()->findByPk(Yii::app()->user->ultimaHistoria);
         $paciente=Persona::model()->findByPk($historia->id_p);

         $infoMedico=utf8_decode("Dr(a). ".$medico->nombre." ".$medico->apellido."\n CI ".$medico->ci."\n No. Registro ".$medico->registro."\n ");    
         $infoPaciente=utf8_decode($paciente->nombre." ".$paciente->apellido."\n CI ".$paciente->cedula."\nfecha: ".$this->fecha_rec);

          return array('infoPaciente'=>$infoPaciente,'infoMedico'=>$infoMedico,'infoCentro'=>$infoCentro);
        }


/*  
         $infoMedico='';
         $infoPaciente='';
	 $recipe=$this->loadModel($id);         

         $medico=Usuario::model()->findByPk(Yii::app()->user->id_u);
         $historia=Historia::model()->findByPk(Yii::app()->user->ultimaHistoria);
         $paciente=Persona::model()->findByPk($historia->id_p);

         $infoMedico=$medico->nombre." ".$medico->apellido."\n CI".$medico->ci."\n No. Registro ".$medico->registro."\n ";    
         $infoPaciente=$paciente->nombre." ".$paciente->apellido."\n CI ".$paciente->cedula."\nfecha ".$this->fecha_rec;
 */

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Recipe the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
