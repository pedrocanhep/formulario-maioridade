<?php
$resultado = '';
$nome = '';
$dataNascimento = '';
$idade = null;
$mostrarFormulario = true;

function montarResultado($nome, $idade)
{
  $nomeFormatado = htmlspecialchars($nome, ENT_QUOTES, 'UTF-8');
  if ($idade < 18) {
    return '<div class="resultado-card p-4 text-center text-white bg-danger rounded-4 shadow">
            <p class="fs-5">Olá, ' . $nomeFormatado . '! Sinto muito mas não posso deixar você entrar, você tem ' . $idade . ' anos e é de menor!</p>
            <div class="emoji">🔞</div>
        </div>';
  }

  return '<div class="resultado-card p-4 text-center text-white bg-success rounded-4 shadow">
        <p class="fs-5">Olá, ' . $nomeFormatado . '. Seja muito bem vindo(a)!<br>Por ter ' . $idade . ', você é de maior, então pode entrar!</p>
        <div class="emoji emoji-ola">👋</div>
    </div>';
}

$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $nome = trim($_POST['nome'] ?? '');
  $dataNascimento = $_POST['data-nascimento'] ?? '';

  if ($nome === '' || $dataNascimento === '') {
    $resultado = '<div class="alert alert-warning">Preencha todos os campos.</div>';
  } elseif (mb_strlen($nome) < 3) {
    $resultado = '<div class="alert alert-warning">O nome deve conter pelo menos 3 caracteres.</div>';
  } else {
    try {
      $data = new DateTime($dataNascimento);
      $idade = (new DateTime())->diff($data)->y;
      $resultado = montarResultado($nome, $idade);
      $mostrarFormulario = false;
    } catch (Exception $e) {
      $resultado = '<div class="alert alert-danger">Data de nascimento inválida.</div>';
    }
  }

  if ($isAjax) {
    echo $resultado;
    exit;
  }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Maioridade</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

  <style>
    body {
      background-color: #2c3e50;
      min-height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 1rem;
      color: #ffffff;
    }

    main {
      width: 624px;
      max-width: 100%;
      background-color: #1f2937;
      border-radius: 1.5rem;
      padding: 2rem;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.25);
    }

    .emoji {
      font-size: 6rem;
      margin: 2rem auto 0;
      line-height: 1;
    }

    .botao-voltar {
      margin-top: 1.5rem;
      width: fit-content;
    }

    .resultado-card p {
      margin-bottom: 1.5rem;
      font-size: 1.15rem;
      line-height: 1.6;
    }

    .form-floating input::placeholder {
      color: transparent;
    }

    .emoji-ola {
      display: inline-block;
      transform-origin: 70% 90%;
      /* pivô na base da palma */
      animation: acenar 1.8s cubic-bezier(0.4, 0, 0.2, 1) infinite;
    }

    @keyframes acenar {
      0% {
        transform: rotate(0deg);
      }

      10% {
        transform: rotate(14deg);
      }

      20% {
        transform: rotate(-8deg);
      }

      30% {
        transform: rotate(14deg);
      }

      40% {
        transform: rotate(-4deg);
      }

      50% {
        transform: rotate(10deg);
      }

      60% {
        transform: rotate(0deg);
      }

      100% {
        transform: rotate(0deg);
      }
    }
  </style>
</head>

<body>
  <h1 class="mb-4 text-center">Cadastro de pessoa</h1>
  <main>
    <div id="mensagem-erro"></div>

    <div id="form-container" <?php echo $mostrarFormulario ? '' : 'style="display:none;"'; ?>>
      <form id="envio-dados" action="" method="post" class="mb-4">
        <div class="mb-3 form-floating">
          <input type="text" id="nome" name="nome" class="form-control" placeholder="Nome" value="<?php echo htmlspecialchars($nome, ENT_QUOTES, 'UTF-8'); ?>">
          <label for="nome">nome</label>
        </div>
        <div class="mb-3">
          <label for="data-nascimento" class="form-label">Data de nascimento</label>
          <input type="date" name="data-nascimento" id="data-nascimento" class="form-control" value="<?php echo htmlspecialchars($dataNascimento, ENT_QUOTES, 'UTF-8'); ?>">
        </div>
        <button type="submit" class="btn btn-primary w-100 py-3">CADASTRAR</button>
      </form>
    </div>

    <div id="resultado" <?php echo $mostrarFormulario ? 'style="display:none;"' : ''; ?>>
      <?php if (!$mostrarFormulario && $resultado !== '') : ?>
        <?php echo $resultado; ?>
      <?php endif; ?>
    </div>
    <a id="voltar" href="" class="btn btn-light botao-voltar" style="display: <?php echo $mostrarFormulario ? 'none' : 'block'; ?>;">Me cadastrar novamente</a>
  </main>

  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
  <script>
    $(document).ready(function() {
      $('#envio-dados').on('submit', function(event) {
        event.preventDefault();
        $('#mensagem-erro').html('');

        var nome = $('#nome').val().trim();
        var dataNascimento = $('#data-nascimento').val();

        if (nome === '' || dataNascimento === '') {
          $('#mensagem-erro').html('<div class="alert alert-warning">Preencha todos os campos.</div>');
          return;
        }

        if (nome.length < 3) {
          $('#mensagem-erro').html('<div class="alert alert-warning">O nome deve conter pelo menos 3 caracteres.</div>');
          return;
        }

        $.ajax({
          url: '',
          method: 'POST',
          data: {
            nome: nome,
            'data-nascimento': dataNascimento
          },
          success: function(response) {
            $('#form-container').hide();
            $('#resultado').html(response).show();
            $('#voltar').show();
          },
          error: function() {
            $('#mensagem-erro').html('<div class="alert alert-danger">Ocorreu um erro no envio. Tente novamente.</div>');
          }
        });
      });

      $('#voltar').on('click', function(event) {
        if ($(this).css('display') !== 'none') {
          event.preventDefault();
          $('#resultado').hide();
          $('#form-container').show();
          $(this).hide();
        }
      });
    });
  </script>
</body>

</html>