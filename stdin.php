<?php
/* ************************************************************************** */
/*                                                                            */
/*                                                        :::      ::::::::   */
/*   stdin.php                                          :+:      :+:    :+:   */
/*                                                    +:+ +:+         +:+     */
/*   By: apergens <apergens@student.42.fr>          +#+  +:+       +#+        */
/*                                                +#+#+#+#+#+   +#+           */
/*   Created: 2014/06/29 22:51:45 by apergens          #+#    #+#             */
/*   Updated: 2014/07/01 14:18:57 by apergens         ###   ########.fr       */
/*                                                                            */
/* ************************************************************************** */

$cmd = '';                                                                      // Mise à zéro de l'historique de commande
$msg_type = 1;                                                                  // Type de message, identique à celui du serveur

$connect = false;
$server = __DIR__.'/secu.php';                                                  // Chemin vers le serveur avant de demander la connexion
if (file_exists($server) && ($msg_key = ftok($server, 'G')) != -1)              // Demande si une connexion automatique est nécessaire
{
  do
  {
    if (!msg_queue_exists($msg_key))
      break ;
    termpos(0, 0);                                                              // Remet la position du curseur à zero
    termerase(true, true);                                                      // Efface le contenu du terminal
    $connect = trim(readline('Connexion automatique au serveur '.$msg_key.' [Y/n]? '));
  }
  while (($connect = check_string($connect)) === -1);                           // Redemande si la réponse est incorrect
}

if (!$connect)                                                                  // Passe la demande de l'ID serveur si demandé
  $msg_key = intval(readline('ID du serveur: '));
$msg_id = msg_get_queue($msg_key);                                              // Récupère l'accès à la file de messages

do
{
  termpos(0, 0);                                                                // Remet la position du curseur à zero
  termerase(true, true);                                                        // Efface le contenu du terminal

  $str_id = ' ID: '.$msg_key;                                                   // Chaine contenant l'ID
  $first = explode(' ', $cmd);                                                  // Construction de la chaine contenant l'historique
  $rest = implode(' ', array_slice($first, 1));
  $str_history = $cmd ? "+ \e[".(!$checked ? '38;5;160;4m' : '4m').$first[0]."\e[0m ".$rest : '';
  $size = 90 - strlen($str_id);                                                 // Taille max de l'historique en largeur
  if (strlen($str_history) >= $size)                                            // Tronquage de l'historique si nécessaire
    $str_history = substr($str_history, 0, $size - 3).'...';

  echo $str_history;                                                            // Affiche l'historique
  termpos(1, $size);
  echo "\e[38;5;240m".$str_id."\e[0m";                                          // Affiche l'ID du serveur

  termpos(2, 0);                                                                // Se positionne pour écrire le prompt
  $cmd = trim(readline(' $> '));                                                // Demande la commande à envoyer
  if (($checked = check_cmd($cmd)))                                             // Vérifie la cohérence de la commande
    msg_send($msg_id, $msg_type, $cmd, false, false, $msg_err);                 // Envoi la commande si tout est bon
}
while ($cmd != 'exit' || !msg_queue_exists($msg_key));                          // Boucle sur le prompt tant qu'il n'est pas demandé de quitter

function check_cmd($cmd)                                                        // Vérifie la cohérence d'une commande
{
  $return = false;
  $cmd = explode(' ', $cmd);
  $count = count($cmd) - 1;

  if ($cmd[0] == 'exit' && !$count)
    $return = true;
  else if ($cmd[0] == 'stats' && $count >= 1)
    $return = true;
  else if ($cmd[0] == 'say' && $count >= 1)
    $return = true;
  else if ($cmd[0] == 'beep' && (!$count || ($count == 1 && is_numeric($cmd[1]))))
    $return = true;
  else if (($cmd[0] == 'enable' || $cmd[0] == 'disable') && $count == 1)
  {
    switch ($cmd[1])
    {
      case 'intra':
      case 'count':
      case 'date':
        $return = true;
    }
  }

  return ($return);
}

function check_string($str, $default = true)                                    // Vérifie la réponse à une question simple
{
  $len = strlen($str);
  if (!$len)
    return ($default);
  else if ($len == 1 && stripos('yn', $str) !== false)
    return (($str[0] == 'Y' || $str[0] == 'y') ? true : false);
  return (-1);
}

function termpos($row, $col, $force = false, $str = null)                       // Position le curseur en fonction du choix
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
?>
