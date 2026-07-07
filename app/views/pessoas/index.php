<?php
$tituloPagina = 'Pessoas';
require __DIR__ . '/../layouts/header.php';
?>

<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
    <div>
        <h1 class="h3 mb-1">Pessoas atendidas</h1>
        <p class="text-secondary mb-0">Cadastro, edição e inativação sem excluir o histórico.</p>
    </div>

    <button class="btn btn-success" type="button" onclick="novaPessoa()">
        Nova pessoa
    </button>
</div>

<div id="alerta"></div>

<div class="card border-0 shadow-sm mb-4 d-none" id="cardFormulario">
    <div class="card-body">
        <h2 class="h5" id="tituloFormulario">Nova pessoa</h2>

        <form id="formPessoa">
            <input type="hidden" name="id" id="pessoaId">

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Nome *</label>
                    <input class="form-control" type="text" name="nome" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">E-mail *</label>
                    <input class="form-control" type="email" name="email" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Telefone</label>
                    <input class="form-control" type="text" name="telefone">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Documento (CPF/RA) *</label>
                    <input class="form-control" type="text" name="documento" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label">Curso</label>
                    <input
                        type="text"
                        class="form-control"
                        name="curso">
                </div>

                <div class="col-md-6">
                    <label class="form-label">Período</label>

                    <input
                        type="text"
                        class="form-control"
                        name="periodo">
                </div>

                <div class="col-md-4">
                    <label class="form-label">Status *</label>
                    <select class="form-select" name="status" required>
                        <option value="ativo">Ativo</option>
                        <option value="inativo">Inativo</option>
                    </select>
                </div>
            </div>

            <div class="d-flex gap-2 mt-3">
                <button class="btn btn-success" type="submit">Salvar</button>
                <button class="btn btn-outline-secondary" type="button" onclick="fecharFormulario()">
                    Cancelar
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Nome</th>
                    <th>Documento</th>
                    <th>E-mail</th>
                    <th>Curso</th>
                    <th>Período</th>
                    <th>Status</th>
                    <th class="text-end">Ações</th>
                </tr>
            </thead>
            <tbody id="tabelaPessoas">
                <tr>
                    <td colspan="6" class="text-center py-4">Carregando...</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const formPessoa = document.getElementById('formPessoa');
const cardFormulario = document.getElementById('cardFormulario');
const tituloFormulario = document.getElementById('tituloFormulario');

function novaPessoa() {
    tituloFormulario.textContent = 'Nova pessoa';
    document.getElementById('pessoaId').value = '';
    formPessoa.reset();
    cardFormulario.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

function fecharFormulario() {
    cardFormulario.classList.add('d-none');
    formPessoa.reset();
}

async function carregarPessoas() {
    try {
        const resposta = await AtendeLabApi.get('pessoas', 'listar');
        const pessoas = AtendeLabApi.toList(resposta);
        const tbody = document.getElementById('tabelaPessoas');

        if (!pessoas.length) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="text-center py-4">Nenhuma pessoa cadastrada.</td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = pessoas.map(pessoa => {
            const classeStatus = pessoa.status === 'ativo' ? 'text-bg-success' : 'text-bg-secondary';
            const dadosJson = AtendeLabApi.escapeAttr(JSON.stringify(pessoa));

            return `
<tr>
        <td>${AtendeLabApi.escape(pessoa.nome)}</td>

            <td>${AtendeLabApi.escape(pessoa.documento)}</td>
            <td>${AtendeLabApi.escape(pessoa.email)}</td>
            <td>${AtendeLabApi.escape(pessoa.curso ?? '')}</td>
            <td>${AtendeLabApi.escape(pessoa.periodo ?? '')}</td>
                <td>
                    <span class="badge ${classeStatus}">
                        ${AtendeLabApi.escape(pessoa.status)}
                    </span>
                </td>
                <td class="text-end">
                    <button class="btn btn-sm btn-outline-primary"
                        onclick="editarPessoa(${pessoa.id})">
                        Editar
                    </button>

                   <button class="btn btn-sm btn-outline-danger"
                        onclick="inativarPessoa(${pessoa.id})">
                        Inativar
                    </button>
        </td>
</tr>
`;
        }).join('');
    } catch (error) {
        AtendeLabApi.showAlert('alerta', error.message, 'danger');
    }
}

function editarPessoa(dadosJson) {
    alert("Clique em editar!");
    const pessoa = JSON.parse(dadosJson);
    tituloFormulario.textContent = 'Editar pessoa';
    
    document.getElementById('pessoaId').value = pessoa.id;
    formPessoa.querySelector('[name="nome"]').value = pessoa.nome ?? '';
    formPessoa.querySelector('[name="email"]').value = pessoa.email ?? '';
    formPessoa.querySelector('[name="telefone"]').value = pessoa.telefone ?? '';
    formPessoa.querySelector('[name="documento"]').value = pessoa.documento ?? '';
    formPessoa.querySelector('[name="status"]').value = pessoa.status ?? 'ativo';
    formPessoa.querySelector('[name="curso"]').value =
    pessoa.curso ?? '';

formPessoa.querySelector('[name="periodo"]').value =
    pessoa.periodo ?? '';

    cardFormulario.classList.remove('d-none');
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

formPessoa.addEventListener('submit', async event => {
    event.preventDefault();
    const id = document.getElementById('pessoaId').value;
    const acao = id ? 'atualizar' : 'criar';

    try {
        await AtendeLabApi.post('pessoas', acao, new FormData(formPessoa));
        AtendeLabApi.showAlert('alerta', id ? 'Pessoa atualizada com sucesso.' : 'Pessoa cadastrada com sucesso.');
        fecharFormulario();
        await carregarPessoas();
    } catch (error) {
        AtendeLabApi.showAlert('alerta', error.message, 'danger');
    }
});
async function inativarPessoa(id) {

    if (!confirm("Deseja realmente inativar esta pessoa?")) {
        return;
    }

    try {

        await AtendeLabApi.post(
            "pessoas",
            "inativar",
            { id }
        );

        AtendeLabApi.showAlert(
            "alerta",
            "Pessoa inativada com sucesso."
        );

        carregarPessoas();

    } catch (error) {

        AtendeLabApi.showAlert(
            "alerta",
            error.message,
            "danger"
        );

    }

}

document.addEventListener('DOMContentLoaded', carregarPessoas);
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>