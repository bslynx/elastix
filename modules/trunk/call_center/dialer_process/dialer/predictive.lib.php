<?php
// Autor: Edgar Landivar. 2007
// El presente archivo muestra cómo determinar cuántas llamadas son necesarias originar
// dado que se conoce el promedio y la desviacion estandar de una campaña.
// Partimos de que conocemos el tiempo que lleva hablando cada agente. Estos tiempos
// son provistos en un arreglo

Class predictivo {

  var $astman;
  function predictivo(&$astman)
  {
/*
    require_once "/var/lib/asterisk/agi-bin/phpagi-asmanager.php";
    $this->astman = new AGI_AsteriskManager();
    $this->astman->connect("127.0.0.1", 'admin' , 'elastix456');
*/
    $this->astman = $astman;
  }

  function predecirNumeroLlamadas($cola, $predictive=true)
  {
      $campaniaPromedio        = 75; // Promedio de duracion (en segundos) de las llamadas de esta campania
      $campaniaDesviacion      = 17; // Desviacion (en segundos) de las llamadas de esta campania
      $tiempoContestacion      = 8;  // Promedio del tiempo (en segundos) de contestacion de las llamadas. Este es un parametro fijo.
                                     // Es el tiempo desde que la llamada conecta hasta que el cliente contesta
      $probabilidadSerAtendido = 97; // Es la prob. de ser atendido por un agente. Las politicas en USA y Canada dicen que solo un 3% de las llamadas
                                     // pueden ser 'dropped' debido a que un agente no contesto a tiempo.

      $arrTiemposAgentes=$this->obtenerEstadisticasAgentesCola($cola);

      /* El arreglo $arrTiemposAgentes luce similar al sgte. ejemplo:
         Array (
             [members] => Array (
                     [88888] => Array (
                             [line] => Agent/88888 (Not in use) has taken 3 calls (last was 1107 secs ago)
                             [status] => canBeCalled
                         )
                 )
             [callers] =>
         ) */
  
      if(!is_array($arrTiemposAgentes)) return false; // No se si esto tenga sentido porque el false se puede confundir con el 0.
                                                      // Habra que manejar los errores de otra manera
  
      $llamadas=0;
  
      // Me barro el arreglo $arrTiemposAgentes['members']
  
      foreach($arrTiemposAgentes['members'] as $agente=>$arrAgente) {
          if($arrAgente['status']=="canBeCalled") $llamadas++;
          if($arrAgente['status']=="inUse" and $predictive==true) {
              $duracionLuegoDeContestacion = $arrAgente['currCallTime']+$tiempoContestacion; // La duracion que tendra la llamada si se esperara el tiempo
                                                                                             // de contestacion
              $prob = $this->probabilidadNormalAcumulada($duracionLuegoDeContestacion, $campaniaPromedio, $campaniaDesviacion);
              if($prob>=$probabilidadSerAtendido) $llamadas++;
          }
  
      }
  
      // Ahora reviso si hay callers, esto es llamadas encoladas. Tengo que restar estas llamadas de las que voy a generar.
      // TODO!!!. Pendiente esta parte
  
  
      return $llamadas;
  }
  
  function probabilidadNormalAcumulada($x, $promedio=0, $desviacionEstandar=1) 
  {
  
      $z = ($x-$promedio)/$desviacionEstandar;
      $zabs = abs($z);
  
      // Esta tabla ha sido tomada de http://www.math.unb.ca/~knight/utility/NormTble.htm
      $arrNormalAcumuladaEstandar = array(
    '0.00'=>0.5000,'0.01'=>0.5040,'0.02'=>0.5080,'0.03'=>0.5120,'0.04'=>0.5160,'0.05'=>0.5199,'0.06'=>0.5239,'0.07'=>0.5279,'0.08'=>0.5319,'0.09'=>0.5359,     '0.10'=>0.5398,'0.11'=>0.5438,'0.12'=>0.5478,'0.13'=>0.5517,'0.14'=>0.5557,'0.15'=>0.5596,'0.16'=>0.5636,'0.17'=>0.5675,'0.18'=>0.5714,'0.19'=>0.5753,
    '0.20'=>0.5793,'0.21'=>0.5832,'0.22'=>0.5871,'0.23'=>0.5910,'0.24'=>0.5948,'0.25'=>0.5987,'0.26'=>0.6026,'0.27'=>0.6064,'0.28'=>0.6103,'0.29'=>0.6141,
    '0.30'=>0.6179,'0.31'=>0.6217,'0.32'=>0.6255,'0.33'=>0.6293,'0.34'=>0.6331,'0.35'=>0.6368,'0.36'=>0.6406,'0.37'=>0.6443,'0.38'=>0.6480,'0.39'=>0.6517,
    '0.40'=>0.6554,'0.41'=>0.6591,'0.42'=>0.6628,'0.43'=>0.6664,'0.44'=>0.6700,'0.45'=>0.6736,'0.46'=>0.6772,'0.47'=>0.6808,'0.48'=>0.6844,'0.49'=>0.6879,
    '0.50'=>0.6915,'0.51'=>0.6950,'0.52'=>0.6985,'0.53'=>0.7019,'0.54'=>0.7054,'0.55'=>0.7088,'0.56'=>0.7123,'0.57'=>0.7157,'0.58'=>0.7190,'0.59'=>0.7224,
    '0.60'=>0.7257,'0.61'=>0.7291,'0.62'=>0.7324,'0.63'=>0.7357,'0.64'=>0.7389,'0.65'=>0.7422,'0.66'=>0.7454,'0.67'=>0.7486,'0.68'=>0.7517,'0.69'=>0.7549,
    '0.70'=>0.7580,'0.71'=>0.7611,'0.72'=>0.7642,'0.73'=>0.7673,'0.74'=>0.7704,'0.75'=>0.7734,'0.76'=>0.7764,'0.77'=>0.7794,'0.78'=>0.7823,'0.79'=>0.7852,
    '0.80'=>0.7881,'0.81'=>0.7910,'0.82'=>0.7939,'0.83'=>0.7967,'0.84'=>0.7995,'0.85'=>0.8023,'0.86'=>0.8051,'0.87'=>0.8078,'0.88'=>0.8106,'0.89'=>0.8133,
    '0.90'=>0.8159,'0.91'=>0.8186,'0.92'=>0.8212,'0.93'=>0.8238,'0.94'=>0.8264,'0.95'=>0.8289,'0.96'=>0.8315,'0.97'=>0.8340,'0.98'=>0.8365,'0.99'=>0.8389,
    '1.00'=>0.8413,'1.01'=>0.8438,'1.02'=>0.8461,'1.03'=>0.8485,'1.04'=>0.8508,'1.05'=>0.8531,'1.06'=>0.8554,'1.07'=>0.8577,'1.08'=>0.8599,'1.09'=>0.8621,
    '1.10'=>0.8643,'1.11'=>0.8665,'1.12'=>0.8686,'1.13'=>0.8708,'1.14'=>0.8729,'1.15'=>0.8749,'1.16'=>0.8770,'1.17'=>0.8790,'1.18'=>0.8810,'1.19'=>0.8830,
    '1.20'=>0.8849,'1.21'=>0.8869,'1.22'=>0.8888,'1.23'=>0.8907,'1.24'=>0.8925,'1.25'=>0.8944,'1.26'=>0.8962,'1.27'=>0.8980,'1.28'=>0.8997,'1.29'=>0.9015,
    '1.30'=>0.9032,'1.31'=>0.9049,'1.32'=>0.9066,'1.33'=>0.9082,'1.34'=>0.9099,'1.35'=>0.9115,'1.36'=>0.9131,'1.37'=>0.9147,'1.38'=>0.9162,'1.39'=>0.9177,
    '1.40'=>0.9192,'1.41'=>0.9207,'1.42'=>0.9222,'1.43'=>0.9236,'1.44'=>0.9251,'1.45'=>0.9265,'1.46'=>0.9279,'1.47'=>0.9292,'1.48'=>0.9306,'1.49'=>0.9319,
    '1.50'=>0.9332,'1.51'=>0.9345,'1.52'=>0.9357,'1.53'=>0.9370,'1.54'=>0.9382,'1.55'=>0.9394,'1.56'=>0.9406,'1.57'=>0.9418,'1.58'=>0.9429,'1.59'=>0.9441,
    '1.60'=>0.9452,'1.61'=>0.9463,'1.62'=>0.9474,'1.63'=>0.9484,'1.64'=>0.9495,'1.65'=>0.9505,'1.66'=>0.9515,'1.67'=>0.9525,'1.68'=>0.9535,'1.69'=>0.9545,
    '1.70'=>0.9554,'1.71'=>0.9564,'1.72'=>0.9573,'1.73'=>0.9582,'1.74'=>0.9591,'1.75'=>0.9599,'1.76'=>0.9608,'1.77'=>0.9616,'1.78'=>0.9625,'1.79'=>0.9633,
    '1.80'=>0.9641,'1.81'=>0.9649,'1.82'=>0.9656,'1.83'=>0.9664,'1.84'=>0.9671,'1.85'=>0.9678,'1.86'=>0.9686,'1.87'=>0.9693,'1.88'=>0.9699,'1.89'=>0.9706,
    '1.90'=>0.9713,'1.91'=>0.9719,'1.92'=>0.9726,'1.93'=>0.9732,'1.94'=>0.9738,'1.95'=>0.9744,'1.96'=>0.9750,'1.97'=>0.9756,'1.98'=>0.9761,'1.99'=>0.9767,
    '2.00'=>0.9772,'2.01'=>0.9778,'2.02'=>0.9783,'2.03'=>0.9788,'2.04'=>0.9793,'2.05'=>0.9798,'2.06'=>0.9803,'2.07'=>0.9808,'2.08'=>0.9812,'2.09'=>0.9817,
    '2.10'=>0.9821,'2.11'=>0.9826,'2.12'=>0.9830,'2.13'=>0.9834,'2.14'=>0.9838,'2.15'=>0.9842,'2.16'=>0.9846,'2.17'=>0.9850,'2.18'=>0.9854,'2.19'=>0.9857,
    '2.20'=>0.9861,'2.21'=>0.9864,'2.22'=>0.9868,'2.23'=>0.9871,'2.24'=>0.9875,'2.25'=>0.9878,'2.26'=>0.9881,'2.27'=>0.9884,'2.28'=>0.9887,'2.29'=>0.9890,
    '2.30'=>0.9893,'2.31'=>0.9896,'2.32'=>0.9898,'2.33'=>0.9901,'2.34'=>0.9904,'2.35'=>0.9906,'2.36'=>0.9909,'2.37'=>0.9911,'2.38'=>0.9913,'2.39'=>0.9916,
    '2.40'=>0.9918,'2.41'=>0.9920,'2.42'=>0.9922,'2.43'=>0.9925,'2.44'=>0.9927,'2.45'=>0.9929,'2.46'=>0.9931,'2.47'=>0.9932,'2.48'=>0.9934,'2.49'=>0.9936,
    '2.50'=>0.9938,'2.51'=>0.9940,'2.52'=>0.9941,'2.53'=>0.9943,'2.54'=>0.9945,'2.55'=>0.9946,'2.56'=>0.9948,'2.57'=>0.9949,'2.58'=>0.9951,'2.59'=>0.9952,
    '2.60'=>0.9953,'2.61'=>0.9955,'2.62'=>0.9956,'2.63'=>0.9957,'2.64'=>0.9959,'2.65'=>0.9960,'2.66'=>0.9961,'2.67'=>0.9962,'2.68'=>0.9963,'2.69'=>0.9964,
    '2.70'=>0.9965,'2.71'=>0.9966,'2.72'=>0.9967,'2.73'=>0.9968,'2.74'=>0.9969,'2.75'=>0.9970,'2.76'=>0.9971,'2.77'=>0.9972,'2.78'=>0.9973,'2.79'=>0.9974,
    '2.80'=>0.9974,'2.81'=>0.9975,'2.82'=>0.9976,'2.83'=>0.9977,'2.84'=>0.9977,'2.85'=>0.9978,'2.86'=>0.9979,'2.87'=>0.9979,'2.88'=>0.9980,'2.89'=>0.9981,
    '2.90'=>0.9981,'2.91'=>0.9982,'2.92'=>0.9982,'2.93'=>0.9983,'2.94'=>0.9984,'2.95'=>0.9984,'2.96'=>0.9985,'2.97'=>0.9985,'2.98'=>0.9986,'2.99'=>0.9986,
    '3.00'=>0.9987,'3.01'=>0.9987,'3.02'=>0.9987,'3.03'=>0.9988,'3.04'=>0.9988,'3.05'=>0.9989,'3.06'=>0.9989,'3.07'=>0.9989,'3.08'=>0.9990,'3.09'=>0.9990,
    );

      $prob=0;
      foreach($arrNormalAcumuladaEstandar as $Z => $P) {
          if($Z>=$zabs) {
              $prob=$P;
              break;
          }
      }
  
      if($z<0) $prob=1-$P;
  
      return $prob*100;
  }
  
  function obtenerEstadisticasAgentesCola($cola)
  {
      $strShowQueue = $this->astman->Command("show queue $cola");
      $arrCallers = array();
  
      $arrShowQueue = split("\n", $strShowQueue['data']);
  
      $seccion="";
      $arrSalida=array();
      $arrMembers=array();
      foreach($arrShowQueue as $line) {
          if(ereg("^[[:space:]]*Members:", $line)) {
              $seccion="members";
          } else if(ereg("^[[:space:]]*Callers:", $line)) {
              $seccion="callers";
          }
  
          switch($seccion) {
              case 'members':
                  if(eregi("^[[:space:]]*(Agent/([[:digit:]]+))@?", $line, $arrReg)) {
                      $arrMembers[$arrReg[2]]['line']=trim($line);
  
                      /* Fragmento del archivo main/devicestate.c
                         0 AST_DEVICE_UNKNOWN       "Unknown",      Valid, but unknown state
                         1 AST_DEVICE_NOT_INUSE     "Not in use",   Not used 
                         2 AST_DEVICE IN USE        "In use",       In use 
                         3 AST_DEVICE_BUSY          "Busy",         Busy 
                         4 AST_DEVICE_INVALID       "Invalid",      Invalid - not known to Asterisk 
                         5 AST_DEVICE_UNAVAILABLE   "Unavailable",  Unavailable (not registred) 
                         6 AST_DEVICE_RINGING       "Ringing",      Ring, ring, ring 
                         7 AST_DEVICE_RINGINUSE     "Ring+Inuse",   Ring and in use 
                         8 AST_DEVICE_ONHOLD        "On Hold"       On Hold */
  
                      if(ereg("\(Not in use\)", $line) or ereg("\(Ringing\)", $line)) {
                          $arrMembers[$arrReg[2]]['status']='canBeCalled';
                      } else if(ereg("\(In use\)", $line) or ereg("\(Busy\)", $line) or ereg("\(Ring+Inuse\)", $line) or ereg("\(On Hold\)", $line)) {
                          $arrMembers[$arrReg[2]]['status']='inUse';
                          $arrMembers[$arrReg[2]]['currCallTime']=time()-$this->tiempoAgenteEnLlamada($arrReg[2]);
                      } else {
                          $arrMembers[$arrReg[2]]['status']='unAvailable';
                      }
  
                  }
                  break;
              case 'callers':
                  $arrCallers[]=$line;
                  break;
              default:
  
          }
      }
  
      $arrSalida['members']=$arrMembers;
      $arrSalida['callers']=$arrCallers;
      return $arrSalida;
  }
  
  function obtenerAgentes()
  {
      $strAgentShow = $this->astman->Command("agent show");
      $arrAgentShow = split("\n", $strAgentShow['data']);
  
      foreach($arrAgentShow as $line) {
          if(ereg("^[[:space:]]*([[:digit:]]{2,})", $line, $arrReg)) {
              $arrSalida[$arrReg[1]]=$line;
          }
      }
      return $arrSalida;
  }
  
  function tiempoAgenteEnLlamada($agente)
  {
      // Primero obtengo el canal con el cual el agente esta hablando
      $channel=$this->obtenerCanalAgenteHablando($agente);
  
      // Ahora obtengo el tiempo
  
      $strShowChannel=$this->astman->Command("core show channel $channel");
      $arrShowChannel=split("\n", $strShowChannel['data']);
  
      /* level 1: start=2007-07-26 23:04:24
         level 1: answer=2007-07-26 23:04:28
         level 1: end=2007-07-26 23:04:28 */
  
      $CDRStartTime = NULL;
      foreach($arrShowChannel as $linea) {
          if(ereg("level [[:digit:]]+: start=(.*)", $linea, $arrReg)) {
              $CDRStartTime=$arrReg[1];
          }
      }
  
      if (!is_null($CDRStartTime)) {
          $CDRStartTime=strtotime($CDRStartTime);
      } else {
          print_r($arrShowChannel);
      }
      return $CDRStartTime;
  }
  
  function obtenerCanalAgenteHablando($agente)
  {
      $arrAgentes=@$this->obtenerAgentes();

      $line=$arrAgentes[$agente];
  
      eregi("talking to ((SIP|IAX|ZAP|H323|OH323)/([[:alnum:]\-]{2,}))[[:space:]]+", $line, $arrReg);
      $channel=$arrReg[1];
  
      return $channel;
  }

  // Nota, esta funcion devuelve el numero de agentes que se encuentran usando la linea.
  // Es decir que su estatus es InUse, Busy, Ring+Busy y OnHold.
  // Sin embargo la construi porque se necesitaba saber cuantas lineas de la TRONCAL por la
  // que sale una llamada se encuentran ocupadas.
  // Entonces puede que el resultado de esta funcion difiera del necesitado.
  function numeroAgentesUsandoLaLinea($cola)
  {
      $agentesUsandoLinea=0;
      $arrAgentesCola=$this->obtenerEstadisticasAgentesCola($cola);
      foreach($arrAgentesCola as $agente=>$arrAgente) {
          if($arrAgente['status']=="inUse") {
              $agentesUsandoLinea++;
          }
      }
 
      return $agentesUsandoLinea;
  }
}
?>
