<!DOCTYPE html>
<html>
<head>
    <title>Serch HRBrain Id</title>
    <meta charset="utf-8">
    <style>
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
            padding: 20px;
        }

        .container {
            max-width: 500px;
            margin: 0 auto;
            background-color: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        h1 {
            text-align: center;
            color: #333;
        }

        label {
            font-weight: bold;
            color: #555;
        }

        input[type="text"] {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        input[type="submit"] {
            width: 100%;
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
        }

        input[type="submit"]:hover {
            background-color: #45a049;
        }

        .result {
            text-align: center;
            margin-top: 20px;
            font-size: 18px;
            color: #333;
        }

        .error {
            color: red;
            text-align: center;
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>HRBrain ID Search</h1>
        <form method="post">
            <div class="form-group">
                <label for="mail">メールアドレス</label>
                <input id="mail" type="text" name="mail" value="">
            </div>
            <div class="form-group">
                <label for="employeeNum">従業員番号</label>
                <input id="employeeNum" type="text" name="employeeNum" value="">
            </div>
            <input type="submit" name="btn_submit" value="生成する">
        </form>

        <?php
            if(isset($_POST) && !empty($_POST)){
                $mail=$_POST['mail'];
                $employeeNum=$_POST['employeeNum'];
                $_POST=null;
                
                if($mail=="" && $employeeNum==""){
                    echo "<div class='error'>メールアドレスまたは従業員番号を入力してください。</div>";
                }else{
                    lookup($mail,$employeeNum);
                }
            }

            function lookup($mail,$employee){
                // トークンエンドポイントのURL
                $tokenUrl = 'https://rext.oapi.hrbrain.jp/auth/token'; 

                // APIトークン
                $clienttoken = 'mBOLbo1EUvbeEdJkt8O1EJi4R6TGEXUBFm5AD3wP';

                // POSTデータ
                $postData = [
                    'clientId' => 'rext',
                    'clientSecret' => $clienttoken,
                ];

                // cURLセッションを初期化
                $ch = curl_init();

                // cURLオプションを設定
                curl_setopt($ch, CURLOPT_URL, $tokenUrl);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
                curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

                // リクエストを実行
                $response = curl_exec($ch);

                // エラーチェック
                if (curl_errno($ch)) {
                    echo '<div class="error">Error: ' . curl_error($ch) . '</div>';
                    exit;
                }

                // レスポンスをデコード
                $responseData = json_decode($response, true);
                if (isset($responseData['token'])) {
                    $token = $responseData['token'];
                } else {
                    echo '<div class="error">アクセストークンの取得に失敗しました</div>';
                    $token = -1;
                }

                // cURLセッションを閉じる
                curl_close($ch);    

                // トークンエンドポイントのURL
                $url = 'https://rext.oapi.hrbrain.jp/members/v1/members/lookup'; 

                $headers=array(
                    'Authorization: Bearer '.$token.'',
                    'Content-Type: application/json' 
                );

                // POSTデータ
                if ($mail === null || $mail === '') {
                    $postData = ['employeeNumber' => $employee];
                } else if ($employee === null || $employee === '') {
                    $postData = ['email' => $mail];
                } else {
                    $postData = ['email' => $mail, 'employeeNumber' => $employee];
                }

                // cURLセッションを初期化
                $ch = curl_init();

                // cURLオプションを設定
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

                // リクエストを実行
                $response = curl_exec($ch);

                // エラーチェック
                if (curl_errno($ch)) {
                    echo '<div class="error">Error: ' . curl_error($ch) . '</div>';
                    exit;
                }

                // レスポンスをデコード
                $response = json_decode($response, true);
                // echo "<pre>";
                // print_r($response);
                // echo "</pre>";


                // cURLセッションの終了
                curl_close($ch);

                // HRBrain ID の表示
                if (isset($response['id'])) {
                    $id = $response['id'];
                } else {
                    $id = 'IDが見つかりませんでした';
                }

                echo "<div class='result'>この方のHRBrain IDは <strong>$id</strong> です。</div>";
            }
        ?>
    </div>
</body>
</html>
