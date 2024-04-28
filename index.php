<?php
function generatePlayfairKey($keyword) {
    $alphabet = "abcdefghiklmnopqrstuvwxyz"; // 'j' dihilangkan (I/J dianggap sama)
    $key = "";
    $keyword = strtolower(preg_replace('/[^a-z]/', '', $keyword));
    $keyword = str_replace('j', 'i', $keyword); // Mengganti 'j' dengan 'i'
    $seen = array();
    
    // Membuat key tanpa duplikat
    foreach (str_split($keyword . $alphabet) as $char) {
        if (!isset($seen[$char])) {
            $key .= $char;
            $seen[$char] = true;
        }
    }

    return $key;
}

function prepareText($text, $encrypt = true) {
    $text = strtolower(preg_replace('/[^a-z]/', '', $text));
    $text = str_replace('j', 'i', $text); // Mengganti 'j' dengan 'i'

    if ($encrypt) {
        $newText = '';
        $len = strlen($text);
        for ($i = 0; $i < $len; $i++) {
            $newText .= $text[$i];
            if ($i < $len - 1 && $text[$i] == $text[$i + 1]) {
                $newText .= 'x'; // Menambahkan 'x' jika ada huruf yang berdampingan sama
            }
        }
        $text = $newText;
    }

    // Jika panjang teks ganjil, tambahkan 'x' di akhir
    if (strlen($text) % 2 != 0) {
        $text .= 'x';
    }

    return $text;
}

function playfairCipher($text, $keyword, $encrypt = true) {
    $key = generatePlayfairKey($keyword);
    $preparedText = prepareText($text, $encrypt);
    $textLength = strlen($preparedText);
    $result = '';

    for ($i = 0; $i < $textLength; $i += 2) {
        $pair = substr($preparedText, $i, 2);
        $pos1 = strpos($key, $pair[0]);
        $pos2 = strpos($key, $pair[1]);

        $row1 = intdiv($pos1, 5);
        $col1 = $pos1 % 5;
        $row2 = intdiv($pos2, 5);
        $col2 = $pos2 % 5;

        if ($row1 == $row2) {
            $col1 = ($encrypt) ? ($col1 + 1) % 5 : ($col1 + 4) % 5;
            $col2 = ($encrypt) ? ($col2 + 1) % 5 : ($col2 + 4) % 5;
        } elseif ($col1 == $col2) {
            $row1 = ($encrypt) ? ($row1 + 1) % 5 : ($row1 + 4) % 5;
            $row2 = ($encrypt) ? ($row2 + 1) % 5 : ($row2 + 4) % 5;
        } else {
            $tempCol = $col1;
            $col1 = $col2;
            $col2 = $tempCol;
        }

        $result .= $key[$row1 * 5 + $col1] . $key[$row2 * 5 + $col2];
    }

    return $result;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $text = $_POST["text"];
    $keyword = $_POST["keyword"];
    $operation = $_POST["operation"];

//Ubah teks dan kunci menjadi huruf kecil sebelum diproses
    $text = strtolower($text);
    $keyword = strtolower($keyword);

    $result = playfairCipher($text, $keyword, $operation === "encrypt");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Playfair Cipher</title>
    <link rel="stylesheet" type="text/css" href="style.css">
   
</head>
<body>
    <h1>Playfair Cipher</h1>
    <form method="post">
    <label for="text">Masukkan Teks:</label>
    <input type="text" id="text" name="text" required>
    <label for="keyword">Masukkan Key:</label>
    <input type="text" id="keyword" name="keyword" required>
    <h3>Silahkan pilih salah satu</h3>
    <div class="radio-options">
        <label for="encrypt" class="radio-label">
            <input type="radio" id="encrypt" name="operation" value="encrypt" checked>
            <span class="radio-custom"></span>
            Enkrip
        </label>
        
        <label for="decrypt" class="radio-label">
            <input type="radio" id="decrypt" name="operation" value="decrypt">
            <span class="radio-custom"></span>
            Dekrip
        </label>
    </div>
    <input type="submit" value="kirim">
</form>


    <?php if (isset($result)): ?>
        <div class="result">
            <h2>Hasil</h2>
            <p><?php echo htmlspecialchars($result); ?></p>
        </div>
    <?php endif; ?>
</body>
</html>
