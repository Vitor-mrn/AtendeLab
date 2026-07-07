<?php

class FrontendController
{
    public function dashboard(): void
    {
        $tituloPagina = 'Dashboard';
        require __DIR__ . '/../views/dashboard/index.php';
    }

    public function pessoas(): void
    {
        $tituloPagina = 'Pessoas';
        require __DIR__ . '/../views/pessoas/index.php';
    }

    public function tipos(): void
    {
        $tituloPagina = 'Tipos de Atendimento';
        require __DIR__ . '/../views/tipos-atendimentos/index.php';
    }

    public function atendimentos(): void
    {
        $tituloPagina = 'Atendimentos';
        require __DIR__ . '/../views/atendimentos/index.php';
    }
}