<?php
  session_start();
?>

<!DOCTYPE html>
<html lang="pt-br">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

    <title>Vendas</title>

    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">

    <!-- Custom fonts for this template -->
    <link href="vendor/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">

    <!-- Plugin CSS -->
    <link href="vendor/datatables/dataTables.bootstrap4.css" rel="stylesheet">
    <link href="css/remodal.css" rel="stylesheet" type="text/css">
    <link href="css/remodal-default-theme.css" rel="stylesheet" type="text/css">

    <!-- Custom styles for this template -->
    <link href="css/sb-admin.css" rel="stylesheet">

    <!-- Favicon -->
    <link rel="shortcut icon" type="image/png" href="img/shopping-icon.png"/>  
  </head>

  <?php
    include ("includes/dbconnect.php");
    $alertMessage = "";

    if (isset($_POST['vendaID'])) //delete
    {
      if (!isset($_SESSION['usuarioID']) != "")
      {
        header("Location: index.php#alertModal");
      }
       
      $vendaID = $_POST['vendaID'];

      $consulta = $conexao->prepare("DELETE FROM vendas_itens WHERE vendaID = ?; DELETE FROM vendas WHERE ID = ?;");
      $consulta->execute(array($vendaID, $vendaID));
      $resultado = $consulta->rowCount();

      if ($resultado == 0)
      {
        $alertMessage = "Falha ao cancelar a venda!";
      }
      else
      {
        $alertMessage = "Venda cancelada com sucesso!";
      }
    }

    //read
    $consulta = $conexao->prepare("SELECT vendas.ID, vendas.Data, sum(produtos.Valor * vendas_itens.Quantidade) AS Total FROM vendas JOIN vendas_itens ON vendas.ID = vendas_itens.VendaID JOIN produtos ON produtos.ID = vendas_itens.ProdutoID GROUP BY vendas_itens.VendaID");
    $consulta->execute();
    $registros = $consulta->fetchAll();
  ?>

  <body class="fixed-nav sticky-footer bg-dark" id="page-top">

    <?php include ('includes/menu.php'); ?>

    <div class="content-wrapper">
      <div class="container-fluid">

        <!-- Breadcrumbs -->
        <ol class="breadcrumb">
          <li class="breadcrumb-item"><a href="index.php">Meu Mercado</a></li>
          <li class="breadcrumb-item active">Vendas</li>
        </ol>

        <div class="col-md-12">
          <a href="criar-venda.php" class="btn btn-success form-group btnCreate" style="margin-left: 1rem;">
            <span><i class="fa fa-dollar" aria-hidden="true"></i> Nova Venda</span>
          </a>
                
          <br>
          
          <table id="dataTable" class="table table-bordered" width="100%" id="dataTable" cellspacing="0">
            <thead>
              <tr>
                <th width="50px">Código</th>
                <th>Total</th>
                <th>Data</th>
                <th width="50px">Gerenciar</th>
              </tr>
            </thead>

            <tfoot>
              <tr>
                <th>Código</th>
                <th>Total</th>
                <th>Data</th>
                <th width="50px">Gerenciar</th>
              </tr>
            </tfoot>

            <tbody>
              <?php
                foreach ($registros as $key => $value)
                {
                  echo "<tr>";
                  echo  "<td class='venda vendaID' data-id='" . $value['ID'] . "'>" . $value['ID'] . "</td>";
                  echo  "<td class='venda vendaTotal'>" . $value['Total'] . "</td>";
                  echo  "<td class='venda vendaData'>" . $value['Data'] . "</td>";
                  echo  "<td class='text-center'><a " . (isset($_SESSION['usuarioID']) != "" ? "href='#deleteModal'" : "href='login.php' data-toggle='tooltip' data-placement='left' title='Você precisa estar logado para deletar.'") . " class='btnDelete'><i class='fa fa-trash' aria-hidden='true'></i></a></td>";
                  echo "</tr>";
                }
              ?>
            </tbody>
          </table>
          <br>
        </div>
      </div>
    </div>

    <div class="remodal" data-remodal-id="deleteModal">
      <form action="vendas.php#alertModal" method="post">
        <input type="hidden" name="vendaID" value="">
        <button data-remodal-action="close" class="remodal-close"></button>
        <h2>Deseja cancelar esta venda?</h2>
        <p class="deleteVenda"></p>
        <br>
        <button data-remodal-action="cancel" class="remodal-cancel">Não</button>
        <button type="submit" class="remodal-confirm">Sim</button>
      </form>
    </div>

    <div class="remodal" data-remodal-id="alertModal">
      <button data-remodal-action="close" class="remodal-close"></button>
      <h2><?php echo $alertMessage; ?></h2>
      <br>
      <button data-remodal-action="confirm" class="remodal-confirm">OK</button>
    </div>

    <?php include ('includes/footer.html'); ?>

    <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/popper/popper.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.min.js"></script>

    <!-- Plugin JavaScript -->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>
    <script src="vendor/datatables/jquery.dataTables.js"></script>
    <script src="vendor/datatables/dataTables.bootstrap4.js"></script>
    <script src="js/jquery.mask.min.js"></script>
    <script src="js/bootstrapValidator.min.js"></script>
    <script src="js/remodal.js"></script>

    <!-- Custom scripts for this template -->
    <script src="js/sb-admin.js"></script>

    <script>
      $(document).ready(function(){
        $("#dataTable").DataTable({
          "language": {
            "url": "json/Portuguese-Brasil.json"
          },
          "aoColumnDefs": [
            { "bSearchable": false, "aTargets": [ 3 ] },
            { "bSortable": false, "aTargets": [ 3 ] }
          ]
        });

        $(".venda").click(function() {
          window.location.href = window.location.pathname.replace("vendas.php", "") + "venda.php?id=" + $(this).parent().find('.vendaID').data("id");
        });

        $(".vendaTotal").mask("000.000.000.000.000,00", {reverse: true});

        //$(".vendaData").mask("dd/MM/yyyy");

        var today = new Date();
        var dd = today.getDate();
        var mm = today.getMonth() + 1; //January is 0!

        var yyyy = today.getFullYear();

        if (dd < 10) {
            dd = '0' + dd;
        }

        if (mm < 10) {
            mm = '0' + mm;
        }

        var today = dd + '/' + mm + '/' + yyyy;
        
        $("input[name='vendaData']").val(today);
        
        $(".btnDelete").click(function() {
          var item = $(this).closest("tr");
          var vendaID = $(item).find(".vendaID").data("id");

          $("input[name='vendaID']").val(vendaID);
          $(".deleteVenda").empty().append("Código: " + vendaID);
        });
      });
    </script>
  </body>
</html>