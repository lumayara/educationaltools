<?php
$url_path = $_SERVER["DOCUMENT_ROOT"] . "/comp/ProjetoOlimpiada";
include_once "$url_path/dao/TestDAO.php";
include_once "$url_path/dao/CompetitionDAO.php";

    $id = $_GET['id'];
    $dao = new TestDAO();
    $competition = $dao->get($id)->getCompetition();

    if ($dao->remove($id)) {
        // O usuário e a senha digitados foram validados, manda pra página interna
          header("Location: competition.php?id=".$competition->getId());
          //  echo"$id deu certo";
    }else{
          header("Location: competition.php?id=".$competition->getId());
        //echo"$id nao deu certo";
    }

