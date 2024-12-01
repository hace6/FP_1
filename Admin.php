<?php
include 'db.php';

// Обработка добавления нового ассортимента
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_assorty'])) {
        $name = $_POST['name'];
        $stmt = $db->prepare("INSERT INTO assorty (name) VALUES (:name)");
        $stmt->execute(['name' => $name]);
    }

    if (isset($_POST['add_tovar'])) {
        $name = $_POST['t_name'];
        $assorty_id = $_POST['assorty_id'];
        $flavors = $_POST['flavors'];
        $price = $_POST['price'];
        $stmt = $db->prepare("INSERT INTO tovar (name, assorty_id, price, path, flavors) VALUES (:name, :assorty_id, :price, :path, :flavors)");
        $stmt->execute(['name' => $name, 'assorty_id' => $assorty_id, 'price' => $price, "path" => hash("sha256", $name), "flavors" => $flavors]);
        
        // Сохранение фото товара
        $photoPath = 'TovarPhoto/' . basename(hash("sha256", $name)) . ".png";
        move_uploaded_file($_FILES['tovar_photo']['tmp_name'], $photoPath);

        // Редирект после добавления товара, чтобы избежать повторной отправки формы
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit();
    }

    if (isset($_POST['delete_assorty'])) {
        $id = $_POST['id'];
        $stmt = $db->prepare("DELETE FROM assorty WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }

    if (isset($_POST['delete_tovar'])) {
        $id = $_POST['id'];
        
        // Получаем путь к фотографии товара перед его удалением
        $stmt = $db->prepare("SELECT path FROM tovar WHERE id = :id");
        $stmt->execute(['id' => $id]);
        $product = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($product) {
            // Определяем путь к фото товара
            $photoPath = 'TovarPhoto/' . $product['path'] . '.png';
            
            // Удаляем фото, если оно существует
            if (file_exists($photoPath)) {
                unlink($photoPath); // Удаление файла
            }
        }

        // Удаляем товар из базы данных
        $stmt = $db->prepare("DELETE FROM tovar WHERE id = :id");
        $stmt->execute(['id' => $id]);
    }
}


// Получаем данные для отображения
$assorty = $db->query("SELECT * FROM assorty")->fetchAll(PDO::FETCH_ASSOC);
$tovars = $db->query("SELECT * FROM tovar")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="style.css" rel="stylesheet">
    <title>Админка</title>
</head>
<style>
.h1-style {
  font-size: 2rem; /* Размер шрифта, как у <h1> */
  font-weight: bold; /* Сделаем жирным, как у <h1> */
  text-decoration: none; /* Подчеркивание, как у ссылки */
  color: inherit; /* Цвет текста унаследован от родителя, чтобы не менять его */
  border: 0;
}
</style>
<body class="bg-dark text-white">
    <div class="container mt-5">

        <h1 class="text-white text-center">Администрирование</h1>
        <hr>
        <button class="btn btn-link px-0 h1-style" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAssortyForm" aria-expanded="false" aria-controls="collapseAssortyForm">
            Добавление ассортимента
        </button>
        <div class="collapse"  id="collapseAssortyForm">
        <h2 class="text-center">Добавить ассортимент</h2>
        <form method="POST" class="row g-3 col-md-6 mx-auto">
            <div class="col-12">
                <label for="name" class="form-label">Название ассортимента</label>
                <input type="text" name="name" placeholder="Название ассортимента" class="form-control" required>
            </div>
            <div class="col-12">
                <button type="submit" name="add_assorty" class="btn btn-primary mt-1 ms-auto me-0 w-100">Добавить</button>
            </div>
        </form>
        </div>
        <hr>
        <button class="btn btn-link px-0 h1-style" type="button" data-bs-toggle="collapse" data-bs-target="#collapseAssorty" aria-expanded="false" aria-controls="collapseAssorty">
            Посмотреть добавленный ассортимент
        </button>
        <ul id="collapseAssorty" class="collapse list-group mt-3">
            <?php foreach ($assorty as $item): ?>
                <li class="mt-3 rounded list-group-item bg-light d-flex flex-nowrap justify-content-between">
                    <?php echo $item['name']; ?>
                    <form method="POST" class="d-inline">
                        <input type="hidden" name="id" value="<?php echo $item['id']; ?>">
                        <button type="submit" name="delete_assorty" class="btn btn-danger btn-sm">Удалить</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
        <hr>
        <button class="btn btn-link px-0 h1-style" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTovarFrom" aria-expanded="false" aria-controls="collapseTovarFrom">
            Добавление товара
        </button>
        <div class="collapse"  id="collapseTovarFrom">
        <h2 class="mt-5 text-center">Добавить товар</h2>
        <form method="POST" enctype="multipart/form-data" class="row g-3 col-md-6 mx-auto">
            <div class="col-12">
                <label for="t_name" class="form-label">Название товара</label>
                <input type="text" name="t_name" id="t_name" class="form-control" placeholder="Название товара" required>
            </div>

            <div class="col-12">
                <label for="price" class="form-label">Цена</label>
                <input type="text" name="price" id="price" class="form-control" placeholder="Цена" required>
            </div>

            <div class="col-12">
                <label for="flavors" class="form-label">Вкусы</label>
                <textarea id="flavors" name="flavors" rows="5" class="form-control" placeholder="Вкусы" required></textarea>
            </div>

            <div class="col-12">
                <label for="tovar_photo" class="form-label">Фото товара</label>
                <input type="file" name="tovar_photo" id="tovar_photo" class="form-control" accept="image/*" required>
            </div>

            <div class="col-12">
                <label for="assorty_id" class="form-label">Выберите ассортимент</label>
                <select name="assorty_id" id="assorty_id" class="form-select" required>
                    <option value="">Выберите ассортимент</option>
                    <?php foreach ($assorty as $item): ?>
                        <option value="<?php echo $item['id']; ?>"><?php echo $item['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-12">
                <button type="submit" name="add_tovar" class="btn btn-primary w-100">Добавить</button>
            </div>
        </form>
        </div>
        <hr>
        <button class="mt-2 btn btn-link px-0 h1-style" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTovar" aria-expanded="false" aria-controls="collapseTovar">
            Посмотреть добавленные товары
        </button>
        <ul class="collapse list-group mt-3 border-0" id="collapseTovar">
            <?php foreach ($tovars as $product): ?>
                <li class="list-group-item bg-light d-flex  flex-wrap justify-content-between align-content-end my-3 rounded-end w-50 mx-auto d-block">
                    <?php  echo "<img width='auto' height='200px' src='TovarPhoto/".hash('sha256',$product['name']).".png'></img>"; echo $product['name']; echo $product['price']; ?>
                    
                    <form method="POST" class="d-inline align-self-end">
                        <input type="hidden" name="id" value="<?php echo $product['id']; ?>">
                        
                        <button type="submit" name="delete_tovar" class="btn btn-danger btn-sm">Удалить</button>
                    </form>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.0-beta3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
