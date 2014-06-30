<?php
/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   secu.php                                           :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: apergens <apergens@student.42.fr>          +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2014/06/30 10:55:44 by apergens          #+#    #+#             */
/*   Updated: 2014/06/30 19:40:23 by apergens         ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

$msg_type = 1;
echo "\e[8;5;90;t";                                                             // Redimensionnement automatique

if(($msg_key = ftok(__FILE__, 'G')) == -1)                                      // Création du tunnel de communication
  die('Erreur lors de la création du tunnel de communication !');               // Sinon afficher l'erreur en relation
$msg_id = msg_get_queue($msg_key);                                              // Récupérer l'accès à la file de messages
$server = 'ID: '.strval($msg_key);

$intra_active = true;                                                           // Ouvrir l'intra du pirate
$count_active = true;                                                           // Active le compteur d'intrusions
$date_active = true;                                                            // Active la date de la dernière intrusion
//Message envoyé au pirate (par defaut, modifiable en paramètre)
$msg = 'Message : Merci \'%%\', ta tentative de hack vient d\'être enregistrée';
$msg = (($argc >= 1 && strlen($argv[$argc - 1]) > 5) ? $argv[$argc - 1] : $msg).' - le bocal.';

`stty -echo`;                                                                   // Désactive le mode echo du terminal
termcur();                                                                      // Cacher le curseur
osasay('SecuPlus SSH started');                                                 // Annonce le lancement de SecuPlus SSH

state(-1);                                                                      // Initialise le détecteur
$count = 0;                                                                     // Initialise le compteur d'intrusion

do
{
  $size = termsize();                                                           // Récupère la taille du terminal
  if ($size['cols'] != 90 || $size['rows'] != 5)                                // Si la taille n'est pas celle attendu
    dispsize($size);                                                            // On affiche le message pour demander le redimensionnement
  else if (count(($users = users())))                                           // Si une connexion est détectée ...
  {
    if (!is_array(($memo = state(null))) || $memo['uid'] != $users[0]['uid'])   // Nouvelle détection
    {
      $count++;
      $memo = state($users[0]);                                                 // Enregistre la connexion en cours
      `cat ~/.ssh/know_hosts | grep "^vogsphere" > ~/.ssh/know_hosts`;          // Supprime l'utilisateur de la liste des clients
      $url = 'https://intra.42.fr/user/'.$memo['uid'];                          // Prépare la page de profil de l'étudiant 42
      if ($intra_active)
        `open $url`;                                                            // Ouvre la page dans le navigateur par défaut
    }
    echo termput(str_repeat(' ', 90)."\n", 160, 0);                             // Les lignes suivantes affichent l'alerte
    echo termput('   '.str_repeat('/', 84)."   \n", 160, 0);
    echo termput('   '.str_repeat('/', 17).' ALERTE !! CONNEXION EXTERNE AU TERMINAL DÉTECTÉE '.str_repeat('/', 17)."   \n", 160, 0);
    echo termput('   '.str_repeat('/', 84)."   \n", 160, 0);
    echo termput(str_repeat(' ', 90), 160, 0);
  }
  else if (state(null) !== false)
  {
    state(false);                                                               // Supprime la connexion enregistrée
    $space = str_repeat(' ', 30);                                               // Les lignes suivantes affichent le message de base
    echo termput($space.'   ___        ___         ____'.$space."\n", 34, 0);
    echo termput($space.'  / _ \      / _ |       / __/'.$space."\n", 34, 0);
    echo termput($space.' / , _/     / __ |      _\ \  '.$space."\n", 34, 0);
    echo termput($space.'/_/|_|     /_/ |_|     /___/  '.$space."\n", 34, 0);
    echo termput(str_repeat(' ', 90), 34, 0);

    if (!client_cmd())                                                          // Les lignes suivantes seront en surimpression
    {
      termpos(1, 90 - strlen($server));
      echo termput($server, 240, 0);                                            // Affiche l'ID du serveur pour le client
    }
    termpos(5, 2);
    if ($count_active && !$count)
      echo termput('aucune intrusion', 240, 0);
    else if ($count_active)
      echo termput($count.' intrusion'.(($count > 1) ? 's' : ''), 240, 0);
  }
  usleep(250000);                                                               // Courte attente pour ne pas surcharger le processeur
  $cmd = read_cmd();                                                            // Récupère les dernières commandes
  exec_cmd($cmd);                                                               // Exécution de la commande reçu
}
while ($cmd != 'exit');                                                         // Boucle tant que l'utilisateur n'a pas demandé de quitter

termcur(false);                                                                 // Réinitialisation du curseur avant sortie du programme
`stty echo`;                                                                    // Réactive le mode echo du terminal

function exec_cmd($cmd)                                                         // Exécution d'une commande
{
  state(-1);
  $cmd = explode(' ', $cmd);
  if ($cmd[0] == 'beep')
    echo "\007";
  else if ($cmd[0] == 'say')
  {
    unset($cmd[0]);
    osasay(implode(' ', $cmd));
  }
}

function client_cmd()                                                           // Vérification de la présence d'un client
{
  global $msg_id, $msg_type, $msg_err;
  return (false);                                                               // Surcharge (non fonctionnel en l'état)

  $msg_stat = msg_stat_queue($msg_id);
  $count = $msg_stat['msg_qnum'];

  msg_send($msg_id, $msg_type + 1, 'ping', false, false, $msg_err);
  usleep(250000);

  $msg_stat = msg_stat_queue($msg_id);
  if ($msg_stat['msg_qnum'] == $count)
    return (true);
  return (false);
}

function read_cmd()                                                             // Ecoute les clients sur le tunnel
{
  global $msg_id, $msg_type, $msg_err;

  $msg_stat = msg_stat_queue($msg_id);
  if (!$msg_stat['msg_qnum'])
    return (false);
  msg_receive($msg_id, $msg_type, $msg_type_tmp, 255, $cmd, false, false, $msg_err);
  if ($msg_type_tmp == $msg_type)
    return ($cmd);
  return (false);
}

function state($state)                                                          // Garde l'etat de l'affichage (évite les scintillement)
{
  static $memo;

  if ($state !== null || $state == -1)
  {
    if ($state != -1)
      $memo = $state;
    termpos(0, 0);                                                              // Remet la position du curseur à zero
    termerase(false, true);                                                     // Efface le terminal
  }
  return ($memo);
}

function dispsize($size)                                                        // Demande un redimensionnement si la taille n'est pas bonne
{
  $lines = round($size['rows'] / 2, 0, PHP_ROUND_HALF_DOWN);
  $center = round(($size['cols'] - 32) / 2, 0, PHP_ROUND_HALF_DOWN);
  $center = ($center > 0) ? str_repeat(' ', $center) : '';
  for ($i = (($size['rows'] % 2) ? 0 : -1); $i < $lines; $i++)
    echo termput(str_repeat(' ', 90)."\n", 25, 0);
  echo termput($center.' /!\\ DIMENSIONS INCORRECTES /!\\ '.$center."\n", 25, 0);
  for ($i = 0; $i < $lines - 1; $i++)
    echo termput(str_repeat(' ', 90)."\n", 25, 0);
  $str = ' X != 90 ('.$size['cols'].')  -  Y != 5 ('.$size['rows'].') ';
  $center = round(($size['cols'] - strlen($str)) / 2, 0, PHP_ROUND_HALF_DOWN);
  $center = ($center > 0) ? str_repeat(' ', $center) : '';
  echo termput($center.$str.$center, 88, 0);
  state(-1);                                                                    // Demande un rafraichissement de l'affichage
}

function users($ntty = false)                                                   // Ecoute les connexion SSH
{
  $new = array();
  $ps = @explode("\n", `ps aux | grep sshd: | grep -v grep`);
  for ($i = 0, $count = count($ps); is_array($ps) && $i < $count; $i++)
  {
    if (!strlen($ps[$i]))
      continue ;

    $ps[$i] = array_filter(explode(' ', preg_replace('{( )\1+}', '$1', $ps[$i])));
    if (($key = array_search('sshd:', $ps[$i])))
    {
      $key++;
      $tty = false;
      if (($pos = strpos($ps[$i][$key], '@')) !== false)
      {
        $tty = substr($ps[$i][$key], $pos + 1);
        $ps[$i][$key] = substr($ps[$i][$key], 0, $pos);
      }
      if ($tty || (!$tty && $ntty))
        $new[] = array(
          'usr' => $ps[$i][0],
          'pid' => $ps[$i][1],
          'uid' => $ps[$i][$key],
          'tty' => $tty
        );
    }
  }
  return ($new);
}

function termcur($hide = true)                                                  // Affiche ou pas le curseur dans le terminal
{
  echo "\e[?25".($hide ? 'l' : 'h');
}

function termput($str, $color = 15, $back = false)                              // Ecris en couleur dans le terminal
{
  $color = "\e[38;5;".$color.'m';
  $back = ($back !== false) ? "\e[48;5;".$back.'m' : '';
  return ($back.$color.$str."\e[0m");
}

function termsize()                                                             // Récupère les dimensions du terminal
{
  static $size = array('cols' => 90, 'rows' => 5);

  if (strpos('Can\'t get', ($cmd = `resize 2> /dev/null `)) === false)
    preg_match('/[A-Z]*=(?<cols>\d+);\n[A-Z]*=(?<rows>\d+);.*?/', $cmd, $tmp);
  if (is_array($tmp) && isset($tmp['cols']) && isset($tmp['rows']))
    $size = array('cols' => $tmp['cols'], 'rows' => $tmp['rows']);
  return ($size);
}

function termpos($row, $col, $force = false, $str = null)                       // Repositionne le curseur dans le terminal
{
  echo "\e[".$row.';'.$col.($force ? 'f' : 'H');
  if ($str !== null)
    echo $str;
}

function termerase($line = false, $force)                                       // Efface le contenu du terminal
{
  if (!$line && $force)
    `clear`;
  else
    echo "\e[2".($line ? 'K' : 'J');
}

function osascript($str)                                                        // Envoi une commande osascript
{
  `osascript -e '$str'`;
}

function osasay($str, $voice = 'Kathy')                                         // Fait parler OS X (fantaisie)
{
  $str = str_replace('\'', '', $str);
  $voice = str_replace('\'', '', $voice);
  osascript('say "'.addslashes($str).'" using "'.addslashes($voice).'"');
}
?>
