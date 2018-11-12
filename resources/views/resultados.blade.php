<!doctype html>
<html lang="{{ app()->getLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Resultados</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css?family=Raleway:100,600" rel="stylesheet" type="text/css">

        <!-- Styles -->
        <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">

    </head>
    <body>
        <div class="container">
            <h1 class="text-center">Resultados</h1>
            <br>
            <h3><strong>Modelo:</strong> {{$resultados['modelo']}} </h3>
            <br>
            <h4><strong>Exactitud: </strong> {{ $resultados['precision']*100 }}%</h4>
            <br>

            <h3 class="text-center">Matriz de Confusión</h3>
            <br>
            <table class="table">
              <thead>
                <tr>
                  <th scope="col"></th>
                  <th scope="col">Phishing (Predicción)</th>
                  <th scope="col">Inofensivo (Predicción)</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th scope="row">Phishing (Real)</th>
                  <td>(VP) {{ $resultados['matriz'][0][0] }}</td>
                  <td>(FP) {{ $resultados['matriz'][0][1] }}</td>
                </tr>
                <tr>
                  <th scope="row">Inofensivo (Real)</th>
                  <td>(FN) {{ $resultados['matriz'][1][0] }}</td>
                  <td>(VN) {{ $resultados['matriz'][1][1] }}</td>
                </tr>
              </tbody>
            </table>
            <br>
            <h3 class="text-center">Reporte</h3>
            <br>
            <table class="table">
              <thead>
                <tr>
                  <th scope="col"></th>
                  <th scope="col">Phishing</th>
                  <th scope="col">Inofensivo</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <th scope="row">Sensibilidad</th>
                  <td>{{round($resultados['precision_pred'][0]*100,2)}}%</td>
                  <td>{{round($resultados['precision_pred'][1]*100,2)}}%</td>
                </tr>
                <tr>
                  <th scope="row">F1 score</th>
                  <td>{{round($resultados['f1'][0]*100,2)}}%</td>
                  <td>{{round($resultados['f1'][1]*100,2)}}%</td>
                </tr>
                <tr>
                  <th scope="row">Precisión en predicción</th>
                  <td>{{round($resultados['sensibilidad'][0]*100,2)}}%</td>
                  <td>{{round($resultados['sensibilidad'][1]*100,2)}}%</td>
                </tr>
              </tbody>
            </table>

        </div>
    </body>
</html>
