<?php
$url_path = $_SERVER["DOCUMENT_ROOT"] . "/computaria/ProjetoOlimpiada";
include_once "$url_path/dao/AdministradorDAO.php";

    $id = $_GET['id'];
    $dao = new AdministradorDAO();

    if ($dao->removeAdm($id)) {
        // O usuário e a senha digitados foram validados, manda pra página interna
          header("Location: listaAdmin.php");
          //  echo"$id deu certo";
    }else{
          header("Location: listaAdmin.php");
        //echo"$id nao deu certo";
    }
