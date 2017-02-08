<?php
/**
* Clase auxiliar para el manejo de la conexión e interacción con la base de datos. Clase que extiende de MySQLi.
*
* @package   Base
* @author    Agustin Rios Reyes <nitsugario@gmail.com>
* @version   1.2.0
* @copyright Copyright (c) 2016-2017 Agustin Rios Reyes
* @link      
* @license   http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
class Base extends mysqli
{
	/**
		* Servidor de base de datos: Nombre o IP.
		* @var    string 
		* @access private
	*/
	private $lstHost           = 'localhost';

	/**
		* Nombre de la base de datos.
		* @var    string 
		* @access private
	*/
	private $lstNameDB         = 'DASS';

	/**
		* Usuario de base de datos.
		* @var    string 
		* @access private
	*/
	private $lstUser           = 'root';

	/**
		* Clave de usuario de base de datos.
		* @var    string  
		* @access private
	*/
	private $lstPassword       = '';

	/**
		* Define si los errores y notificaciones se escriben en log.
		* @var    boolean 
		* @access private
	*/
	private $lboCreaLog        = true;

	/**
		* Total de filas devueltas por la consulta.
		* @var    integer 
		* @access private
	*/
	private $lnuFilasTotales   = 0;

	/**
		* Total de campos resultantes.
		* @var    integer 
		* @access private
	*/
	private $lnuTotalCampos    = 0;

	/**
		* Id autogenerado que se utilizó en la última consulta Insert o Update.
		* @var    integer 
		* @access private
	*/
	private $lnuIdUltimoInsert = 0;

	/**
		* Obtiene el número de filas afectadas en la última operación MySQL INSERT, UPDATE, REPLACE or DELETE (para Select usar $lnuFilasTotales).
		* @var    integer 
		* @access private
	*/
	private $lnuTotalAfectadas = 0;
	
	/**
		* Guarda el resultado de sentencias Select.
		* @var    array 
		* @access private
	*/
	private $layFilasReultado  = array();
	
	/**
		* Indica deque forma se requieren los resultados de un SELECT. puedeser MYSQLI_ASSOC ="assoc", MYSQLI_NUM ="num" o  MYSQLI_BOTH ="ambo"
		* @var    string 
		* @access private
	*/
	private $lstTipoResultado  = "assoc";
	
	/**
		* Se indica la sentencias Querys a ejecutar (SELECT, UPDATE, DELETE.. ).
		* @var    string 
		* @access public
	*/
	public  $gstQuery          = "";
	
	function __construct()
	{
		//-- Inicializamos el constructor de Mysqli
		parent:: __construct($this->lstHost,$this->lstUser,$this->lstPassword,$this->lstNameDB);
		$this->set_charset("utf8");

		//-- Validar que no ocurra ningún error.
		if ( mysqli_connect_error() )
		{
			if( $this->lboCreaLog )
				error_log( '[ CLASS: Base.php ] [ ERROR: __construct ] conexión con la Base de Datos '.$this->gstNameDB.': Error No. ' . mysqli_connect_errno() . ' : '. mysqli_connect_error());
			
			die('[ CLASS: Base.php ] [ ERROR: __construct ] Error al establecer la conexión con la Base de Datos '.$this->gstNameDB.'.<br>  Error No. ' . mysqli_connect_errno());
		}
	}

	public function mstSetTipoResultado( $pstTipo ){ $this->lstTipoResultado  = $pstTipo;}

	public function mnuGetTotalFilas     (){ return $this->lnuFilasTotales;  }
	public function mnuGetTotalColumnas  (){ return $this->lnuTotalCampos;   }
	public function mnuGetUltimoIdInsert (){ return $this->lnuIdUltimoInsert;}
	public function mnuGetTotalAfectadas (){ return $this->lnuTotalAfectadas;}
	public function mayGetResultadoSelect(){ return $this->layFilasReultado; }
	public function mstGetTipoResultado  (){ return $this->lstTipoResultado; }
	public function mstGetSQLQuery       (){ return $this->gstQuery;         }

	/**
		* Método que ejecuta sentencias SELECT con resultados de múltiples filas como resultado.
		* @param  string  $this->gstQuery
		* @param  boolean $pboDebug       : Iniciar modo Debug del método
		* @param  string  $pstDedonde     : Nombre del método o instancia del cual se esta ejecutando el método.
		* @uses   $this->layFilasReultado : Se asignan el valor a esta variable.
		* @uses   $this->lnuFilasTotales  : Se asignan el valor a esta variable.
		* @uses   $this->lnuTotalCampos   : Se asignan el valor a esta variable.
		* @uses   $this->mboObtenerResultadoSelect()
		* @access protected
		* @return boolean.
		*
	*/
	protected function mboSelectMultipleFilas( $pboDebug = False, $pstDedonde = 'Misma' )
	{
		if( $this->gstQuery != '' )
		{
			if ( $lobDatosQuery  = $this->query( $this->gstQuery ) )
			{
				$this->lnuFilasTotales = $lobDatosQuery->num_rows;
				$this->lnuTotalCampos  = $lobDatosQuery->field_count;

				$this->mboObtenerResultadoSelect( $lobDatosQuery, 'Multiple' );

				return true;
			}
			else
			{
				$this->lnuFilasTotales  = 0;
				$this->lnuTotalCampos   = 0;
				$this->layFilasReultado = array();

				if( $this->lboCreaLog )
					error_log('[ CLASS: Base.php ] [ ERROR: mboSelectMultipleFilas ] Ocurrió el erro No.'.$this->errno.' : '.$this->error.'<br>DE: '.$pstDedonde.'. Al ejecutar el query: '.$this->gstQuery);
				
				if( $pboDebug )
					die('[ CLASS: Base.php ] [ ERROR: mboSelectMultipleFilas ] Ocurrió el erro No.'.$this->errno.' : '.$this->error.'<br>DE: '.$pstDedonde.'.<br>ERROR: Al ejecutar el query: <br><pre>'.$this->gstQuery.'</pre>');
				else
					return false;
			}
		}
		else
		{
			$this->lnuFilasTotales  = 0;
			$this->lnuTotalCampos   = 0;
			$this->layFilasReultado = array();

			if( $this->lboCreaLog )
				error_log('[ CLASS: Base.php ] [ Notificación: mboSelectMultipleFilas ] La función fue ejecutada con un Query vacío.');

			if( $pboDebug )
				die('[ CLASS: Base.php ] [ Notificación: mboSelectMultipleFilas ] La función fue ejecutada con un Query vacío.');
			else
				return false;
		}
	}

	/**
		* Método que ejecuta sentencias  select  que obtienen como resultado una sola fila.
		* @param  string  $this->gstQuery
		* @param  boolean $pboDebug       : Iniciar modo Debug del método.
		* @uses   $this->lnuFilasTotales  : Se asignan el valor a esta variable.
		* @uses   $this->lnuTotalCampos   : Se asignan el valor a esta variable.
		* @uses   $this->mboObtenerResultadoSelect()
		* @access protected
		* @return boolean.
		*
	*/
	protected function maySelectUnaFila( $pboDebug = False )
	{
		if ($lobDatosQuery  = $this->query( $this->gstQuery ) )
		{
			$this->lnuFilasTotales = $lobDatosQuery->num_rows;
			$this->lnuTotalCampos  = $lobDatosQuery->field_count;

			$this->mboObtenerResultadoSelect( $lobDatosQuery, 'Una' );

			return true;
		}
		else
		{
			$this->lnuFilasTotales  = 0;
			$this->lnuTotalCampos   = 0;
			$this->layFilasReultado = array();

			if( $this->lboCreaLog )
				error_log('[ CLASS: Base.php ] [ ERROR: maySelectUnaFila ] Ocurrió el erro No.'.$this->errno.' : '.$this->error.'. Al ejecutar el query: '.$this->gstQuery);
			
			if( $pboDebug )
				die('[ CLASS: Base.php ] [ ERROR: maySelectUnaFila ] Ocurrió el erro No.'.$this->errno.' : '.$this->error.'.<br>ERROR: Al ejecutar el query: <br><pre>'.$this->gstQuery.'</pre>');
			else
				return false;
		}
	}

	/**
		* Método que asigna el total de filas y columnas consultadas en el Query indicado.
		* @param  string  $this->gstQuery
		* @param  boolean $pboDebug       : Iniciar modo Debug del método
		* @uses   $this->lnuFilasTotales  : Se asignan el valor a esta variable.
		* @uses   $this->lnuTotalCampos   : Se asignan el valor a esta variable.
		* @access protected
		* @return boolean
		*
	*/
	protected function mnuFilasTotalesQuery( $pboDebug = False )
	{
		if ( $lobDatosQuery = $this->query($this->gstQuery) )
		{
			if ($lobDatosQuery->num_rows > 0)
			{
				$this->lnuFilasTotales = $lobDatosQuery->num_rows;
				$this->lnuTotalCampos  = $lobDatosQuery->field_count;
				$lobDatosQuery->free();
				return true;
			}
			else
			{
				$this->lnuFilasTotales = $lobDatosQuery->num_rows;
				$this->lnuTotalCampos  = $lobDatosQuery->field_count;
				$lobDatosQuery->free();

				if( $this->lboCreaLog )
					error_log('[ CLASS: Base.php ] [ Notificación: mnuFilasTotalesQuery ] La consulta no regreso ningún valor o fila. Al ejecutar el query: '.$this->gstQuery);
				
				if( $pboDebug )
					die('[ CLASS: Base.php ] [ Notificación: mnuFilasTotalesQuery ] La consulta no regreso ningún valor o fila. Al ejecutar el query: <br><pre>'.$this->gstQuery).'</pre>';
				else
					return false;
			}
		}
		else
		{
			if( $this->lboCreaLog )
				error_log('[ CLASS: Base.php ] [ ERROR: mnuFilasTotalesQuery ] Ocurrió el erro No.'.$this->errno.' : '.$this->error.'. Al ejecutar el query: '.$this->gstQuery);
			
			if( $pboDebug )
				die("[ CLASS: Base.php ] [ ERROR: mnuFilasTotalesQuery ] Ocurrió el error No. ".$this->errno." : ".$this->error.'.<br>ERROR: Al ejecutar el query: <br><pre>'.$this->gstQuery.'</pre>');
			else
				return false;
		}
	}

	/**
		* Obtiene un Array de los valores de un campo especifico de una tabla, de la forma “SELECT Nombre_proceso FROM proceso”.
		* @param  string $this->gstQuery
		* @uses   $this->mboSelectMultipleFilas()
		* @uses   $this->mayGetResultadoSelect()
		* @access protected
		* @return array  $layValores.
		*
	*/
	protected function mayObtenerArrayDeCampos()
	{
		$this->mboSelectMultipleFilas( false, 'mayObtenerArrayDeCampos()' );
		$layDatosQuery = $this->mayGetResultadoSelect();
		$layValores    = array();

		foreach ($layDatosQuery as $layFilas => $layCampos)
		{			
			foreach ($layCampos as $lstValorCampo)
			{
				$layValores[] = $lstValorCampo;
			}
		}
		return $layValores;
	}

	/**
		* Método  para ejecuta sentencias SQL INSERT, DELETE, UPDATE.
		* @param  string  $this->gstQuery
		* @param  Int     $pnuTipoRespuesta : Ya sea boolean o string
		* @param  string  $pstMensajeTrue   : Mensaje de exito del Query
		* @param  string  $pstMensajeFalse  : Mensaje de error del Query
		* @param  bollean $pboDebug         : Para activar el die y muestre el error en la página.
		* @param  string  $pstDedonde       : Nombre del método o instancia del cual se esta ejecutando el método.
		* @access protected
		* @return string/boolean 
		*
	*/
	protected function mboQueryIDU( $pnuTipoRespuesta = 2, $pstMensajeTrue = "Todo OK", $pstMensajeFalse = "Error en el Query", $pboDebug = False, $pstDedonde = 'Misma' )
	{
		if( $this->query($this->gstQuery) )
		{
			$this->lnuIdUltimoInsert = $this->insert_id;
			$this->lnuTotalAfectadas = $this->affected_rows;

			if( $pnuTipoRespuesta == 1 )
				return $pstMensajeTrue;
			else if( $pnuTipoRespuesta == 2 )
				return true;
		}
		else
		{
			$this->lnuIdUltimoInsert = 0;
			$this->lnuTotalAfectadas = 0;

			if( $this->lboCreaLog )
				error_log('[ CLASS: Base.php ] [ ERROR: mboQueryIDU ] Ocurrió el erro No.'.$this->errno.' : '.$this->error.'DE: '.$pstDedonde.'. Al ejecutar el query: '.$this->gstQuery);
			
			if( $pnuTipoRespuesta == 1 )
			{
				if( $pboDebug )
					die("[ CLASS: Base.php ] [ ERROR: mboQueryIDU ] Ocurrió el error No. ".$this->errno." : ".$this->error.'<br>DE: '.$pstDedonde.'.<br>ERROR: Al ejecutar el query: <br><pre>'.$this->gstQuery.'</pre>');
				else
					return $pstMensajeFalse;
			}
			else if( $pnuTipoRespuesta == 2 )
			{
				if( $pboDebug )
					die("[ CLASS: Base.php ] [ ERROR: mboQueryIDU ] Ocurrió el error No. ".$this->errno." : ".$this->error.'<br>DE: '.$pstDedonde.'.<br>ERROR: Al ejecutar el query: <br><pre>'.$this->gstQuery.'</pre>');
				else
					return false;
			}
		}
	}

	/**
		* Inserta información a la tabla, en base a un arreglos de los campos.
		* @param String   $pstNombreTabla : Nombre de la tabla.
		* @param array    $payCampos      : Campos a ser insertados arreglo["CAMPO"] = "valor";
		* @param boolean  $pboDebug       : Indica si la función está en modo debug.
		* <code>
		* 	<?php
		* 		$lobBase 					         = new Base();
		* 	
		* 		$gayCampos = array();
		* 		$gayCampos['ID_TARIFA'] 			= $gnuIdTarifa;
		* 		$gayCampos['NO_TARIFA'] 			= $gstNoTarifa;
		* 		$gayCampos['FECHA_NACIMIENTO'] 		= "30-03-1564";
		* 
		* 		$gboResultado = $lobBase->mboInsertarContenido( "HRB_TABLA", $gayCampos );
		* 	?>
		* </code>
		* @example 
		* @uses    $this->mboQueryIDU()
		* @uses    $this->gstQuery
		* @access  protected
		* @return  boolean
		*
	*/
	protected function mboInsertarContenido( $pstNombreTabla, $payCampos, $pboDebug = false )
	{
		$lnuTotalCampos = count($payCampos);
		$lstResulInsert = NULL;
		$layCampos      = array();
		$layValoresTem  = array();
		$layValores     = array();

		if( $pstNombreTabla != '' && $lnuTotalCampos > 0 )
		{
			$layCampos      = implode( ",", array_keys( $payCampos ) );
			$layValoresTem  = array_values( $payCampos );

			//-- Recorremos los valores para validar cuales son string y agregarles comillas simples.
			foreach ( $layValoresTem as $lnuclave => $lstValor )
			{
				$lstTipo = gettype($lstValor);

				if( $lstTipo == "string")
					$layValores[] = "'".$lstValor."'";
				else
					$layValores[] = $lstValor;
			}

			$layValores     = implode( ",", $layValores );
			$this->gstQuery = 'INSERT INTO '.$pstNombreTabla.' ('.$layCampos.') VALUES('.$layValores.');';
			$lstResulInsert = $this->mboQueryIDU( 2,"","",$pboDebug.'function mboInsertarContenido()' );

			return $lstResulInsert;
		}
		else
		{
			if( $this->lboCreaLog )
				error_log('[ CLASS: Base.php ] [ ERROR: mboInsertarContenido ] El método se ejecutó sin parámetros.');

			if( $pboDebug )
				die("[ CLASS: Base.php ] [ ERROR: mboInsertarContenido ] El método se ejecutó sin parámetros.");
			else
				return false;
		}
	}

	/**
	 	* Actualiza información en la tabla, en base a arreglos de campos y condiciones.
		* @param String   $pstNombreTabla    : Nombre de la tabla.
		* @param array    $payLLavesWhere    : Campos PRIMARY KEY de la tabla arreglo["CAMPO"] = "valor";
		* @param array    $payCamposUpdate   : Campos a ser modificados arreglo["CAMPO"] = "valor";
		* @param String   $pstSeparadorWhere : Separador para sentencias Where.
		* @param boolean  $pboDebug          : Indica si la función está en modo debug.
		* <code>
		* 	<?php
		* 		$gobBase 								= new Base();
		* 		
		* 		$payLLavesWhere 						= array();
		* 		$payLLavesWhere['ID_TARIFA'] 			= $gnuIdTarifa;
		* 			
		* 		$payCamposUpdate = array();
		* 		$payCamposUpdate['NO_TARIFA'] 			= $gstNoTarifa;
		* 		$payCamposUpdate['FECHA_ACTUALIZACION'] = "SYSDATE";
		* 		
		* 		$gboResultado = $gobBase->mboUpdateContenido( "HRB_TABLA", $payCamposUpdate, $payLLavesWhere );
		* 	?>
		* </code>
		* @example 
		* @uses    $this->mboQueryIDU()
		* @uses    $this->gstQuery
		* @uses    $this->mstObtenerCadenaClaveValorArray()
		* @access  protected
		* @return  boolean
		*
	*/
	protected function mboUpdateContenido( $pstNombreTabla, $payCamposUpdate, $payLLavesWhere, $pstSeparadorWhere = ' AND ', $pboDebug = false )
	{
		$lnuTotalCampos     = count( $payCamposUpdate );
		$lnuTotalWhere      = count( $payLLavesWhere );
		$lstResulUpdate     = NULL;
		$lstCamposUpdate    = '';
		$lstCamposWhere     = '';

		if( $pstNombreTabla != '' && $lnuTotalCampos > 0 && $lnuTotalWhere > 0 )
		{
			$lstCamposUpdate = $this->mstObtenerCadenaClaveValorArray( $payCamposUpdate );
			$lstCamposWhere  = $this->mstObtenerCadenaClaveValorArray( $payLLavesWhere , $pstSeparadorWhere );

			$this->gstQuery = "UPDATE ".$pstNombreTabla." SET ".$lstCamposUpdate." WHERE ".$lstCamposWhere;
			$lstResulUpdate = $this->mboQueryIDU( 2,"","",$pboDebug, 'function mboUpdateContenido()' );

			return $lstResulUpdate;
		}
		else
		{
			if( $this->lboCreaLog )
				error_log('[ CLASS: Base.php ] [ ERROR: mboUpdateContenido ] El método se ejecutó sin o con algunos parámetros.');

			if( $pboDebug )
				die("[ CLASS: Base.php ] [ ERROR: mboUpdateContenido ] El método se ejecutó sin o con algunos parámetros.");
			else
				return false;
		}
	}

	/**
		* Elimina información en base a un arreglo
		* @param String  $pstNombreTabla    : Nombre de la tabla.
		* @param array   $payLLavesWhere    : Campos PRIMARY KEY de la tabla arreglo["CAMPO"] = "valor";
		* @param String  $pstSeparadorWhere : Separador para sentencias Where.
		* @param boolean $pboDebug          : Indica si la función está en modo debug.
		* <code>
		* 	<?php
		* 		$layLLavesWhere["ID_MENU"] = 31;
		* 		$layLLavesWhere["ESTATUS"] = 1;
		*
		* 		echo $base->mboDeleteContenido( 'log_t_menu', $layLLavesWhere,' AND ', true);
		* 	?>
		* </code>
		* @example 
		* @uses    $this->mboQueryIDU()
		* @uses    $this->gstQuery
		* @uses    $this->mstObtenerCadenaClaveValorArray()
		* @access  protected
		* @return  boolean
		*
	*/
	protected function mboDeleteContenido( $pstNombreTabla, $payLLavesWhere, $pstSeparadorWhere = ' AND ', $pboDebug = false )
	{
		$lnuTotalWhere      = count( $payLLavesWhere );
		$lstResulDelete     = NULL;
		$lstCamposWhere     = '';

		if( $pstNombreTabla != '' &&  $lnuTotalWhere > 0 )
		{
			$lstCamposWhere  = $this->mstObtenerCadenaClaveValorArray( $payLLavesWhere , $pstSeparadorWhere );

			$this->gstQuery =  "DELETE FROM ".$pstNombreTabla." WHERE ".$lstCamposWhere;
			$lstResulDelete = $this->mboQueryIDU( 2,"","",$pboDebug,'function mboDeleteContenido()' );

			return $lstResulDelete;
		}
		else
		{
			if( $this->lboCreaLog )
				error_log('[ CLASS: Base.php ] [ ERROR: mboDeleteContenido ] El método se ejecutó sin o con algunos parámetros.');

			if( $pboDebug )
				die("[ CLASS: Base.php ] [ ERROR: mboDeleteContenido ] El método se ejecutó sin o con algunos parámetros.");
			else
				return false;
		}
	}

	/**
		* Método que convierte un Array asociado clave-valor en una cadena separada por el separador indicado.
		* @param Array  $payClaveValor : Arreglo con los datos a coambiar a string.
		* @param String $pstSeparador  : Separador de los elementos del array.
		* <code>
		* 	<?php
		* 		$layCamposUpdate["ID_MENU_PADRE"] = 1;
		* 		$layCamposUpdate["NOMBRE"]        = 'menPrue5';
		* 		$layCamposUpdate["TIPO"]          = 'p';
		* 		$layCamposUpdate["DESCRIPCION"]   = 'prueba insert 5';
		*		
		* 		echo "<br>".$base->mstObtenerCadenaClaveValorArray($layCamposUpdate);
		* 		//-- Cadena resultante: ID_MENU_PADRE = 1,NOMBRE = 'menPrue5',TIPO = 'p',DESCRIPCION = 'prueba insert 5'.
		* 	?>
		* </code>
		* @example
		* @access public
		* @return String
		*
	*/
	public function mstObtenerCadenaClaveValorArray( $payClaveValor, $pstSeparador = ',' )
	{   
		$layClaveValor = array();

		foreach ( $payClaveValor as $lstClave => $lstValor )
		{
			$lstTipo = gettype($lstValor);

			if( $lstTipo == "string")
				$layClaveValor[] = $lstClave." = '".$lstValor."'";
			else
				$layClaveValor[] = $lstClave." = ".$lstValor;
		}

		return implode( $pstSeparador, $layClaveValor );
	}

	/**
		* Consulta información en base a una consulta Select,  especificada por los parámetros configurables.
		* @param String $pstNombreTabla      : Nombre de la tabla.
		* @param String $pstCampos           : Campos amostrar en la consulta
		* @param String $pstCondicionWhere   : Condicioenes para la consulta
		* @param String $pstCondicionOrderBy : Orden de la respuesta
		* @param String $pstTipoResultado    : Como se deven regresar los datos consultados "Inteligente, Multiple o Una".
		* <code>
		* 	<?php
		* 		//-- Obtener una consulta con múltiples filas de resultado.
		* 		print_r( $base->maySelectContenido( 'log_t_menu','*','ID_MENU = 1','','Multiple' ) );
		*		echo "<br>";
		* 		//-- Obtener una consulta con una sola fila.
		* 		print_r( $base->maySelectContenido( 'log_t_menu','*','ID_MENU = 1','','Una' ) );
		* 	?>
		* </code>
		* @example 
		* @uses    $this->mboObtenerResultadoSelect()
		* @access  protected
		* @return  Array
		*
	*/
	protected function maySelectContenido( $pstNombreTabla, $pstCampos = '*', $pstCondicionWhere = '', $pstCondicionOrderBy = '', $pstTipoResultado = 'Inteligente' )
	{
		$lstSelect = '';

		if( $pstNombreTabla != '' )
		{
			$lstSelect = 'SELECT '.$pstCampos.' FROM '.$pstNombreTabla;

			if( $pstCondicionWhere != "" )
				$lstSelect = $lstSelect." WHERE ".$pstCondicionWhere;
				
			if( $pstCondicionOrderBy != "" )
				$lstSelect = $lstSelect." ORDER BY ".$pstCondicionOrderBy;

			$this->gstQuery = $lstSelect;

			if( $lobDatosQuery = $this->query( $lstSelect ) )
			{
				$this->lnuFilasTotales = $lobDatosQuery->num_rows;
				$this->lnuTotalCampos  = $lobDatosQuery->field_count;

				if( $pstTipoResultado == 'Inteligente' )
				{
					if( $this->lnuFilasTotales > 1 )
						$pstTipoResultado = 'Multiple';
					else
						$pstTipoResultado = 'Una';
				}
	      		
	      		$this->mboObtenerResultadoSelect( $lobDatosQuery, $pstTipoResultado );

				return $this->layFilasReultado;
			}
			else
			{
				if( $this->lboCreaLog )
					error_log('[ CLASS: Base.php ] [ ERROR: maySelectContenido ] Ocurrió el erro No.'.$this->errno.' : '.$this->error.'. Al ejecutar el query: '.$lstSelect);
				
				if( $pboDebug )
					die('[ CLASS: Base.php ] [ ERROR: maySelectContenido ] Ocurrió el erro No.'.$this->errno.' : '.$this->error.'.<br>ERROR: Al ejecutar el query: <br><pre>'.$lstSelect.'</pre>');
				else
					return false;
			}
		}
		else
		{
			if( $this->lboCreaLog )
				error_log('[ CLASS: Base.php ] [ ERROR: maySelectContenido ] El método se ejecutó sin el Nombre de la Tabla.');

			if( $pboDebug )
				die("[ CLASS: Base.php ] [ ERROR: maySelectContenido ] El método se ejecutó sin el Nombre de la Tabla.");
			else
				return false;
		}
	}

	/**
		* Método de apoyo para obtener los resultados de una consulta Select en modo múltiples filas o en una solo fila o la primera fila de múltiples filas.
		* @param  Objeto $pobResultado       : Objeto resul Mysqli con los datos obtenidos.
		* @param  String $pstMetodoRelustado : Tipo de respuesta que se requiere Multiple o Una Fila.
		* @access private
		* @return Array
		*
	*/
	private function mboObtenerResultadoSelect( $pobResultado, $pstMetodoRelustado )
	{
		if( $pstMetodoRelustado == 'Multiple' )
		{
			//-- Soporte fetch_all (PHP 5 >= 5.3.0, PHP 7)
			if (method_exists('mysqli_result', 'fetch_all'))
			{
				//-- Nota: para que funcione esta obcion (fetch_all()) se tiene que avilitar la extencion mysqlnd.
				if ($this->lstTipoResultado =="assoc")
					$this->layFilasReultado = $pobResultado->fetch_all(MYSQLI_ASSOC);
				else if ($this->lstTipoResultado =="num")
					$this->layFilasReultado = $pobResultado->fetch_all(MYSQLI_NUM);
				else if ($this->lstTipoResultado =="ambo")
					$this->layFilasReultado = $pobResultado->fetch_all(MYSQLI_BOTH);
				else
					$this->layFilasReultado = $pobResultado->fetch_all();
      			
				$pobResultado->free();//--Lliberar la serie de resultados.
			}
			else
			{//-- Soporte para PHP >=5 <= 5.3.0 .
				if ($this->lstTipoResultado =="assoc")
				{
					while($row = $pobResultado->fetch_array(MYSQLI_ASSOC)){$rows[] = $row;}
					$this->layFilasReultado = $rows;
				}
				else if ($this->lstTipoResultado =="num")
				{
					while($row = $pobResultado->fetch_array(MYSQLI_NUM)){$rows[] = $row;}
					$this->layFilasReultado = $rows;
				}
				else if ($this->lstTipoResultado =="ambo")
				{
					while($row = $pobResultado->fetch_array(MYSQLI_BOTH)){$rows[] = $row;}
					$this->layFilasReultado = $rows;
				}
				else
				{
					while($row = $pobResultado->fetch_array()){$rows[] = $row;}
					$this->layFilasReultado = $rows;
				}
      			
				$pobResultado->free();//--Lliberar la serie de resultados.
			}
		}
		else if( $pstMetodoRelustado == 'Una' )
		{
			if ($this->lstTipoResultado == "assoc")
			{
				$this->layFilasReultado  = $pobResultado->fetch_assoc();
				$pobResultado->free();
			}
			else if ($this->lstTipoResultado == "num")
			{
				$this->layFilasReultado  = $pobResultado->fetch_array(MYSQLI_NUM);
				$pobResultado->free();
			}
		}

		return true;
	}

	/**
		* Método que construye un JSON básico clave, valor a partir de una consulta con dos columnas la primera es la clave y la segunda el valor.
		* @param  string $this->gstQuery
		* @uses   $this->mboSelectMultipleFilas()
		* @uses   $this->mstBorraUltimoCaracter()
		* @access protected
		* @return json   $lstJSON
		*
	*/
	protected function mjsCreaJsonClaveValor()
	{
		$lstJSON   = "{";
		$lstValor  = "";
		$lstClave  = "";
		$lnuCuenta = 1;

		$this->mboSelectMultipleFilas( false, 'mjsCreaJsonClaveValor()' );

		foreach ($this->layFilasReultado as $fila => $columnas)
		{
			foreach ($columnas as $columna => $lstValorC)
			{
				if($lnuCuenta == 1)
				{
					$lnuCuenta = 2;
					$lstValor = $lstValorC;
				}
				else if($lnuCuenta == 2)
				{
					$lstClave  = $lstValorC;
					$lnuCuenta = 1;
					$lstJSON   = $lstJSON.'"'.$lstClave.'":"'.$lstValor.'", ';
					$lstValor  = '';
					$lstClave  = "";
				}
			}
		}
		$lstJSON = $this->mstBorraUltimoCaracter($lstJSON,2);
		$lstJSON = $lstJSON."}";

		return $lstJSON;
	}

	/**
		* Método que elimina el numero da caracteres finales indicados por $pnuNumero
		* @param  String  $pstCadena : Cadena a eliminar ultimos caracteres.
		* @param  int     $pnuNumero : Numero de caracteres a eliminar.
		* @access public
		* @return String 
		*
	*/
	public function mstBorraUltimoCaracter($pstCadena,$pnuNumero=1)
	{
		return substr($pstCadena, 0, -$pnuNumero);
	}

	/**
		* obtener un json apartir de una consulta de una unica fila retorna un JSON.
		* @param  string $this->gstQuery
		* @uses   $this->mboSelectMultipleFilas()
		* @uses   $this->mstBorraUltimoCaracter()
		* @access protected
		* @return json
		*
	*/
	protected function mjnCreaJsonClaveValorTabla()
	{
		$jsonR="[";
		$jsonR2="";
		$tmporal="";

		$this->mboSelectMultipleFilas( false, 'mjnCreaJsonClaveValorTabla()' );
		foreach ($this->layFilasReultado as $fila => $columnas)
		{
			$tmporal="";
			$tmporal.="{";
			foreach ($columnas as $columna => $valorC)
			{
				$tmporal.='"'.$columna.'":"'.$valorC.'", ';
			}

			$tmporal=$this->mstBorraUltimoCaracter($tmporal,2);
			$jsonR.=$tmporal."},";
		}

		$jsonR2=$this->mstBorraUltimoCaracter($jsonR,2);
		$jsonR2=$jsonR2."}]";

		return $jsonR2;
	}

	/**
		* Método que devuelve una cadena del estilo 30,10 la cual se utiliza para construir las sentencias
		* SELECT con parámetro LIMIT 30,10, va construyendo las interacciones de acuerdo a la cantidad Total de Registros y el número total de registros por página que se quieran mostrar.
		* Recibe como parámetros el número total de registros de una consulta ($pnuTotalNoregistros) y el total de registros por página que se quieren ($pnuTotalNoregistros) mostrar.
		* Devuelve la interacción si $pnuTotalNoregistros = 25 y $pnuElementosXPagina =5 : (0,5) ,(5,5) (10,5) (15,5) (20,5)
		* @param  int    $pnuTotalNoregistros : Total de Registros
		* @param  int    $pnuElementosXPagina : Número total de registros por página
		* @uses   $this->mstBorraUltimoCaracter()
		* @access protected
		* @return string
		*
	*/
	protected function mstLimitarConsulta($pnuTotalNoregistros,$pnuElementosXPagina)
	{
		$lnuResiduo     = $pnuTotalNoregistros % $pnuElementosXPagina;
		$lnuPaginas     = 0;
		$lnuPaginaInici = 0;
		$lnuPaginaFin   = $pnuElementosXPagina;
		$lstPaginado    = '';

		if ( $lnuResiduo > 0 )
		{
			$divicion   = explode( '.', ($pnuTotalNoregistros / $pnuElementosXPagina) );
			$lnuPaginas = $divicion[0]+1;
		}
		else
		{
			$lnuPaginas = ($pnuTotalNoregistros / $pnuElementosXPagina);
		}

		for ($i=1; $i <= $lnuPaginas; $i++)
		{ 
			if ( $i == $lnuPaginas )
			{
				if ( $lnuPaginaFin > $pnuTotalNoregistros )
				{
					$lnuPaginaFin   = $lnuPaginaFin - ($lnuPaginaFin-$pnuTotalNoregistros);
				}
			}

			$lstPaginado .= "( $lnuPaginaInici, $pnuElementosXPagina ),";
			$lnuPaginaInici = $lnuPaginaFin;
			$lnuPaginaFin   = $lnuPaginaFin+$pnuElementosXPagina;
		}
		return $this->mstBorraUltimoCaracter($lstPaginado);
	}

	/**
		* Método que construye la sentencia SQL SELECT para obtener los resultados dependiendo de 
		* la cantidad de Registros y el número de Registros por consulta mediante el parámetro LIMIT.
		* Recibe como parámetros en nombre de la tabla ($lstTabla), la coordenada ($lstCoordenada) que se 
		* refiere de donde adonde se muestran los registros para LIMIT este valor se puede obtener 
		* del método mstLimitarConsulta().y los registros ($lstDatos) que se quieren obtener de la consulta
		* por default se obtienen todos los registros de la tabla.
		* @param  string $lstTabla      : Nombre de la tabla
		* @param  string $lstCoordenada : Se refiere de donde adonde se muestran los registros para LIMIT
		* @param  string $lstDatos      : Registros que se quieren obtener de la consulta
		* @access protected
		* @return string
		*
	*/
	protected function mstTabularRgistros($lstTabla,$lstCoordenada,$lstDatos = '*')
	{
		$lstSQL ='SELECT '.$lstDatos.' FROM '.$lstTabla.' LIMIT '.$lstCoordenada.';';
		return $lstSQL;
	}

}//-- Termina class Base extends mysqli
?>