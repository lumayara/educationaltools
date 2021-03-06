<?php

// Definindo o tipo de documento como JSON
header('Content-Type: application/json; charset=utf-8');

$url_path = $_SERVER["DOCUMENT_ROOT"] . "/comp/ProjetoOlimpiada";

include_once "$url_path/dao/ParticipantDAO.php";
include_once "$url_path/dao/TestParticipantDAO.php";
include_once "$url_path/dao/AnswersDAO.php";
include_once "$url_path/dao/TestDAO.php";
include_once "$url_path/dao/QuestionDAO.php";
include_once "$url_path/dao/ChoiceDAO.php";

$userDAO = new ParticipantDAO();
$testParticipantDAO = new TestParticipantDAO();
$answersDAO = new AnswersDAO();
$testDAO = new TestDAO();
$questionDAO = new QuestionDAO();
$choiceDAO = new ChoiceDAO();

// Iniciar Sessão
session_start();

// Criar variável de retorno
$jsonReturn;

// Verifica existência de usuário logado
if (isset($_SESSION["user"])) {

    // Resgata usuario da sessão
    $participant = $userDAO->get($_SESSION["user"]);

    // Verificar se existe 
    if (isset($_POST)) {

        // Verificar se existe id para questionário
        if (isset($_POST["id"])) {

            // TestParticipant
            $testParticipant = $testParticipantDAO->get($_POST["id"]);

            // Verificar se parâmetro tipo existe
            if (isset($_POST["type"])) {

                // Verificar se o tipo de informação solicitada é uma questão ou se é a submissão de uma resposta
                if ($_POST["type"] == "QUESTION") { // Solicita uma questão
                    // Recuperar Questões do Questionário não respondidas
                    $questions = $questionDAO->listQuestionsNotAnsweredByTestParticipant($testParticipant->getId());
                    
                    // Verificar se ainda há questoes para responder
                    if (count($questions) > 0) {

                        // Embaralhar questões recuperadas
                        shuffle($questions);

                        // Primeira questão do vetor
                        $question = $questions[0];

                        // Vetor de alternativas
                        $jsonChoices = array();
                        // Alternativas
                        $choices = $choiceDAO->listChoicesByQuestion($question->getId());
                        // Adicionar resultados ao array JSON
                        foreach ($choices as $key => $value) {
                            $jsonChoices[$key] = ["id" => $value->getId(), "choice" => mb_convert_encoding($value->getChoice(), "UTF-8", "Windows-1252")];
                        }

                        // Adicionar mensagem de retorno
                        $jsonReturn = array(
                            "message" => "Questão recuperada com sucesso!",
                            // Adicionar o vetor de testes
                            "question" => array(
                                "id" => $question->getId(),
                                "registrationDate" => $question->getRegistrationDate(),
                                "question" => mb_convert_encoding($question->getQuestion(), "UTF-8", "Windows-1252"),
                                "image" => $question->getImagem(),
                                "topic" => mb_convert_encoding($question->getTopic(), "UTF-8", "Windows-1252"),
                                "points" => $question->getPoints(),
                                "choices" => $jsonChoices
                            ),
                            "qtdeQuestions" => count($questions)
                        );
                    } else {
                        // Finalizar Teste
                        $testParticipant->setFinalized(true);
                        // Atualizar TestParticipant
                        $testParticipantDAO->update($testParticipant);
                        // Adicionar retorno
                        $jsonReturn = array(
                            "message" => "Parabéns! Você terminou o seu teste!",
                            // Pontuação
                            "points" => $testParticipantDAO->getPoints($testParticipant->getTest()->getId(), $testParticipant->getParticipant()->getId()),
                            // Questões
                            "qtdeQuestions" => count($questions)
                        );
                    }
                } else if ($_POST["type"] == "SUBMIT") { // Submete uma resposta
                    if (isset($_POST["questionId"])) {
                        // Resgatar Questão
                        $question = $questionDAO->get($_POST["questionId"]);

                        if (isset($_POST["choiceId"])) {
                            // Resgatar Alternativa
                            $choice = $choiceDAO->get($_POST["choiceId"]);

                            // Adicionar Resposta
                            $answer = new Answers(NULL, $testParticipant, $question, $choice);
                            $answersDAO->add($answer);

                            // Adicionar mensagem de retorno
                            $jsonReturn = array(
                                "message" => "Questão respondida com sucesso!",
                                // Adicionar o vetor de testes
                                "success" => true
                            );
                            
                        } else {
                            $jsonReturn = array("message" => "Não foi possível realizar a operação. Falta a Alternativa.");
                        }
                    } else {
                        $jsonReturn = array("message" => "Não foi possível realizar a operação. Falta a Questão.");
                    }
                }
            } else {
                $jsonReturn = array("message" => "Não foi possível realizar a operação. Falta o tipo de requisição.");
            }
        } else {
            $jsonReturn = array("message" => "Não foi possível recuperar a questão. Identificação não fornecida.");
        }
    } else {
        $jsonReturn = array("message" => "Não foi possível realizar a operação. Acesso negado!");
    }
}
// Retornar valor em formato JSON
echo json_encode($jsonReturn, JSON_UNESCAPED_UNICODE);
