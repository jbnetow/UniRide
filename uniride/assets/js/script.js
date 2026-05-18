/* =====================================================
   UniRide - JavaScript Global
   Validações de formulários, máscaras e confirmações.
===================================================== */

document.addEventListener('DOMContentLoaded', function () {

    // -------------------------------------------------
    // Máscara de CPF: 000.000.000-00
    // -------------------------------------------------
    document.querySelectorAll('input[data-mascara="cpf"]').forEach(function (input) {
        input.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 11);
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d)/, '$1.$2');
            v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
            e.target.value = v;
        });
    });

    // -------------------------------------------------
    // Máscara de telefone: (00) 00000-0000
    // -------------------------------------------------
    document.querySelectorAll('input[data-mascara="telefone"]').forEach(function (input) {
        input.addEventListener('input', function (e) {
            let v = e.target.value.replace(/\D/g, '').slice(0, 11);
            if (v.length > 6) {
                v = v.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
            } else if (v.length > 2) {
                v = v.replace(/^(\d{2})(\d{0,5}).*/, '($1) $2');
            } else if (v.length > 0) {
                v = v.replace(/^(\d{0,2}).*/, '($1');
            }
            e.target.value = v;
        });
    });

    // -------------------------------------------------
    // Máscara de placa: ABC-1D23 ou ABC1D23
    // -------------------------------------------------
    document.querySelectorAll('input[data-mascara="placa"]').forEach(function (input) {
        input.addEventListener('input', function (e) {
            let v = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 7);
            if (v.length > 3) {
                v = v.slice(0, 3) + '-' + v.slice(3);
            }
            e.target.value = v;
        });
    });

    // -------------------------------------------------
    // Validação: e-mail institucional (.edu.br)
    // -------------------------------------------------
    const formCadastro = document.getElementById('form-cadastro');
    if (formCadastro) {
        formCadastro.addEventListener('submit', function (e) {
            const emailInput = formCadastro.querySelector('input[name="email"]');
            const senhaInput = formCadastro.querySelector('input[name="senha"]');
            const confirmaInput = formCadastro.querySelector('input[name="senha_confirma"]');

            const email = emailInput.value.trim().toLowerCase();

            if (!email.endsWith('.edu.br')) {
                e.preventDefault();
                alert('O e-mail deve ser institucional (terminar com .edu.br).');
                emailInput.focus();
                return;
            }

            if (senhaInput && senhaInput.value.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres.');
                senhaInput.focus();
                return;
            }

            if (confirmaInput && senhaInput.value !== confirmaInput.value) {
                e.preventDefault();
                alert('A confirmação de senha não coincide.');
                confirmaInput.focus();
                return;
            }
        });
    }

    // -------------------------------------------------
    // Validação: vagas e valor não negativos no criar carona
    // -------------------------------------------------
    const formCarona = document.getElementById('form-carona');
    if (formCarona) {
        formCarona.addEventListener('submit', function (e) {
            const vagas = parseInt(formCarona.querySelector('input[name="vagas"]')?.value || 0);
            const valor = parseFloat(formCarona.querySelector('input[name="valor"]')?.value || 0);

            if (vagas < 1 || vagas > 6) {
                e.preventDefault();
                alert('Informe entre 1 e 6 vagas.');
                return;
            }
            if (valor < 0) {
                e.preventDefault();
                alert('O valor por passageiro não pode ser negativo.');
                return;
            }

            // valida data: não pode ser no passado
            const dataInput = formCarona.querySelector('input[name="data_viagem"]');
            if (dataInput) {
                const hoje = new Date();
                hoje.setHours(0, 0, 0, 0);
                const dataInformada = new Date(dataInput.value + 'T00:00:00');
                if (dataInformada < hoje) {
                    e.preventDefault();
                    alert('A data da viagem não pode ser no passado.');
                    dataInput.focus();
                    return;
                }
            }
        });
    }

    // -------------------------------------------------
    // Confirmações para ações destrutivas
    // -------------------------------------------------
    document.querySelectorAll('[data-confirma]').forEach(function (el) {
        el.addEventListener('click', function (e) {
            const msg = el.getAttribute('data-confirma') || 'Tem certeza?';
            if (!confirm(msg)) {
                e.preventDefault();
            }
        });
    });

});
