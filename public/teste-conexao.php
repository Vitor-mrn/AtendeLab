<?php

$conn = new mysqli(
    "127.0.0.1",
    "root",
    "",
    "atendelab",
    3306
);

if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}

echo "Conexão realizada com sucesso!";