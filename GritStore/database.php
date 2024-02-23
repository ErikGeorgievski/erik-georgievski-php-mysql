<?php
error_reporting(E_ALL & ~E_WARNING);


$host = 'localhost';
$port = 3306;
$database = 'erik';
$username = 'root';
$password = '';
$connection = new mysqli($host, $username, $password, $database, $port);

if ($connection->connect_error != null) {
    die('Anslutningen misslyckades' . $connection->connect_error);
} else {
    echo "<div>Anslutningen lyckades</div>";
}




// Skapa images tabellen
$query_images = "CREATE TABLE IF NOT EXISTS images (
    img_id INT PRIMARY KEY AUTO_INCREMENT,
    img_url VARCHAR(255),
    img_path VARCHAR(255)
)";
$result_images = $connection->query($query_images);
if ($result_images) {
    echo "Table 'images' created successfully";
} else {
    echo "Error creating images table" . $connection->error;
}

// Lägg till några exempelbilder
$query_insert_images = "INSERT INTO images (img_url, img_path) VALUES
    ('img01.jpg', 'images/img01.jpg'),
    ('img02.jpg', 'images/img02.jpg'),
    ('img03.jpg', 'images/img03.jpg'),
    ('img04.jpg', 'images/img04.jpg'),
    ('img05.jpg', 'images/img05.jpg')";
$result_insert_images = $connection->query($query_insert_images);
if ($result_insert_images) {
    echo "Inserted values into 'images' successfully";
} else {
    echo "Error inserting values into images table" . $connection->error;
}

// Skapa products tabellen
$query_products = "CREATE TABLE IF NOT EXISTS products (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255),
    description VARCHAR(255),
    price FLOAT,
    stock INT,
    img_id INT,
    FOREIGN KEY (img_id) REFERENCES images(img_id)
)";
$result_products = $connection->query($query_products);
if ($result_products) {
    echo "Table 'products' created successfully";
} else {
    echo "Error creating products table" . $connection->error;
}



// Lägg till produkter
$query_insert_products = "INSERT INTO products (name, description, price, stock, img_id) VALUES
    ('GARMIN MARQ Commander Gen 2 Carbon Edition', 'MARQ Commander - Carbon Edition är utformad för dem som ständigt strävar efter excellence.', 39499.00, 50, 1),
    ('GARMIN MARQ Athlete Gen 2 - Performance Edition', 'GARMIN MARQ Athlete Gen 2 - Performance Edition representerar en fusion av lyx och teknologi skräddarsydd för den dedikerade atleten.', 28399.00, 30, 2),
    ('GARMIN MARQ Golfer Gen 2 Carbon Edition', 'MARQ Golfer - Carbon Edition är en modern tool watch som reflekterar din passion för golf.', 38299.00, 20, 3),
    ('GARMIN MARQ Athlete Gen 2 Carbon Edition', 'Garmin MARQ Carbon Edition är en modern tool watch designad för de mest hängivna atleternativ.', 35499.00, 10, 4),
    ('GARMIN MARQ Athlete Gen 2', 'Manufacturer Garmin Model Phoenix Women Men Womens watch Mens watch Glass Power Sapphire Screen Touchscreen.', 20599.00, 5, 5)";
$result_insert_products = $connection->query($query_insert_products);
if ($result_insert_products) {
    echo "Inserted values into 'products' successfully";
} else {
    echo "Error inserting values into products table" . $connection->error;
}

// Skapa customers tabellen
$query_customer = "CREATE TABLE IF NOT EXISTS customers (
    customer_id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(255),
    last_name VARCHAR(255),
    personal_number VARCHAR(255),
    phone VARCHAR(255),
    address VARCHAR(255),
    postal_code VARCHAR(255),
    city VARCHAR(255),
    email VARCHAR(255)
)";
$result_customer = $connection->query($query_customer);
if ($result_customer) {
    echo "Table 'customers' created successfully";
} else {
    echo "Error creating customers table" . $connection->error;
}

// Skapa orders tabellen
$query_orders = "CREATE TABLE IF NOT EXISTS orders (
    order_id INT PRIMARY KEY AUTO_INCREMENT,
    customer_id INT,
    shipping_option_id INT,
    status VARCHAR(255),
    date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    total_amount FLOAT,
    FOREIGN KEY (customer_id) REFERENCES customers(customer_id)
)";
$result_orders = $connection->query($query_orders);
if ($result_orders) {
    echo "Table 'orders' created successfully";
} else {
    echo "Error creating orders table" . $connection->error;
}

// Skapa order_items tabellen
$query_order_items = "CREATE TABLE IF NOT EXISTS order_items (
    order_item_id INT PRIMARY KEY AUTO_INCREMENT,
    order_id INT,
    product_id INT,
    product_name VARCHAR(255),
    quantity INT,
    total_price FLOAT,
    FOREIGN KEY (order_id) REFERENCES orders(order_id),
    FOREIGN KEY (product_id) REFERENCES products(id) 
)";

$result_order_items = $connection->query($query_order_items);
if ($result_order_items) {
    echo "Table 'order_items' created successfully";
} else {
    echo "Error creating order_items table" . $connection->error;
}

// Skapa discount_codes tabellen
$query_discount_codes = "CREATE TABLE IF NOT EXISTS discount_codes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    code VARCHAR(50) UNIQUE,
    amount FLOAT
)";
$result_discount_codes = $connection->query($query_discount_codes);
if ($result_discount_codes) {
    echo "Table 'discount_codes' created successfully";
} else {
    echo "Error creating discount_codes table" . $connection->error;
}

// Skapa shipping_options tabellen
$query_shipping_options = "CREATE TABLE IF NOT EXISTS shipping_options (
    option_id INT PRIMARY KEY AUTO_INCREMENT,
    option_name VARCHAR(255),
    option_price FLOAT
)";
$result_shipping_options = $connection->query($query_shipping_options);

if ($result_shipping_options) {
    echo "Table 'shipping_options' created successfully";
} else {
    echo "Error creating shipping_options table" . $connection->error;
}


// /*FUNKTIONER*/

// Formulär för att lägga till en ny kund och beställning

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit_order"])) {
    // Hämta kunduppgifter från formuläret
    $email = $connection->real_escape_string($_POST["email"]);

    // Kolla om kunden redan finns baserat på e-postadressen
    $query_check_customer = "SELECT customer_id FROM customers WHERE email = '$email'";
    $result_check_customer = $connection->query($query_check_customer);

    if ($result_check_customer === false) {
        die('Error in query: ' . $connection->error);
    } else {
        if ($result_check_customer->num_rows > 0) {
            // Kunden finns redan, hämta kundens ID
            $row = $result_check_customer->fetch_assoc();
            $customer_id = $row["customer_id"];
        } else {
            // Kunden finns inte, lägg till kunden
            $first_name = $connection->real_escape_string($_POST["first_name"]);
            $last_name = $connection->real_escape_string($_POST["last_name"]);
            $personal_number = $connection->real_escape_string($_POST["personal_number"]);
            $phone = $connection->real_escape_string($_POST["phone"]);
            $address = $connection->real_escape_string($_POST["address"]);
            $postal_code = $connection->real_escape_string($_POST["postal_code"]);
            $city = $connection->real_escape_string($_POST["city"]);

            // Lägg till kunden om det finns valda produkter
            $selected_products = isset($_POST["selected_products"]) ? $_POST["selected_products"] : [];
            if (!empty($selected_products)) {
                $query_insert_customer = "INSERT INTO customers (first_name, last_name, personal_number, phone, address, postal_code, city, email) 
                                  VALUES ('$first_name', '$last_name', '$personal_number', '$phone', '$address', '$postal_code', '$city', '$email')";
                $result_insert_customer = $connection->query($query_insert_customer);

                if ($result_insert_customer) {
                    // Hämta det nya kundens ID
                    $customer_id = $connection->insert_id;
                } else {
                    die("Fel vid läggning av kund: " . $connection->error);
                }
            }
        }
    }


    // Hämta de markerade produktid från postdata
    $selected_products = isset($_POST["selected_products"]) ? $_POST["selected_products"] : [];
    $total_amount = 0;



    if (empty($selected_products)) {
        $order_confirmation = "Vänligen välj minst en produkt innan du skickar din beställning.";
    } else {
        // Skapa en ny order
        $selected_shipping_option = $connection->real_escape_string($_POST["shipping_option"]);

        // Lägg till fraktoptionen i INSERT INTO orders-frågan
        $query_insert_order = "INSERT INTO orders (customer_id, shipping_option_id, status, total_amount) VALUES ($customer_id, $selected_shipping_option, 'Processing', 0)";
        $result_insert_order = $connection->query($query_insert_order);

        if ($result_insert_order) {
            $order_id = $connection->insert_id;

            // Loopa igenom markerade produkter och lägg till dem i order_items
            foreach ($selected_products as $product_id) {
                $query_get_product_info = "SELECT name, price FROM products WHERE id = $product_id";
                $result_get_product_info = $connection->query($query_get_product_info);

                if ($result_get_product_info->num_rows > 0) {
                    $product_info = $result_get_product_info->fetch_assoc();
                    $product_name = $connection->real_escape_string($product_info["name"]);
                    $product_price = $product_info["price"];

                    // Lägg till produkten i order_items
                    $query_insert_order_item = "INSERT INTO order_items (order_id, product_id, product_name, quantity, total_price) 
                    VALUES ($order_id, $product_id, '$product_name', 1, $product_price)";
                    $result_insert_order_item = $connection->query($query_insert_order_item);

                    if ($result_insert_order_item) {
                        $total_amount += $product_price;
                    } else {
                        die("Fel vid läggning av order: " . $connection->error);
                    }
                }
            }



            // Uppdatera totalbeloppet för ordern
            $query_update_order_total = "UPDATE orders SET total_amount = $total_amount WHERE order_id = $order_id";
            $result_update_order_total = $connection->query($query_update_order_total);

            if (!$result_update_order_total) {
                die("Fel vid uppdatering av totalbelopp för order: " . $connection->error);
            }

            $order_confirmation = "Order lagd framgångsrikt!";
            $order_success = true;
            var_dump($order_id);

            // Fetch order items for the current customer, including total_amount
            $query_order_items = "SELECT order_items.*, products.name as product_name, products.price as product_price, images.img_path as product_image, orders.total_amount as order_total_amount
            FROM order_items
            INNER JOIN products ON order_items.product_name = products.name
            INNER JOIN orders ON order_items.order_id = orders.order_id
            INNER JOIN images ON products.img_id = images.img_id
            WHERE order_items.order_id = $order_id";

            $result_order_items = $connection->query($query_order_items);

            if ($result_order_items === false) {
                $order_confirmation = "Fel vid hämtning av orderdetaljer: " . $connection->error;
            }
        } else {
            die("Fel vid skapande av order: " . $connection->error);
        }
        // Fetch discount code from post data
        $discount_code = $connection->real_escape_string($_POST["discount_code"]);

        // Kollar om rabatten finns 
        $query_get_discount = "SELECT amount FROM discount_codes WHERE code = '$discount_code'";
        $result_get_discount = $connection->query($query_get_discount);

        if ($result_get_discount->num_rows > 0) {
            // Om rabatten är gödkänd 
            $discount_amount = $result_get_discount->fetch_assoc()["amount"];

            // Loopa igenom valda produkter och uppdatera priserna med rabatten
            foreach ($selected_products as $product_id) {
                $query_get_product_price = "SELECT price FROM products WHERE id = $product_id";
                $result_get_product_price = $connection->query($query_get_product_price);

                if ($result_get_product_price->num_rows > 0) {
                    $product_price = $result_get_product_price->fetch_assoc()["price"];

                    // Applicera rabatten på prise
                    $discounted_price = $product_price - ($product_price * ($discount_amount / 100));

                    // Uppdatera priset i order_items-tabellen
                    $query_update_order_item = "UPDATE order_items SET total_price = $discounted_price WHERE order_id = $order_id AND product_id = $product_id";
                    $result_update_order_item = $connection->query($query_update_order_item);

                    if ($result_update_order_item) {
                        // Uppdatera det totala beloppet för beställningen
                        $total_amount -= ($product_price - $discounted_price);
                        $query_update_order_total = "UPDATE orders SET total_amount = $total_amount WHERE order_id = $order_id";
                        $result_update_order_total = $connection->query($query_update_order_total);

                        if (!$result_update_order_total) {
                            die("Error updating total amount for order: " . $connection->error);
                        }
                    } else {
                        die("Error updating price in order_items: " . $connection->error);
                    }
                }
            }

            // Visa ett meddelande som indikerar att rabatten har tillämpats
            $order_confirmation = "Rabatten har tillämpats, och ordern har skapats med de nya priserna";
        } else {
            // Rabattkoden är ogiltig
            $order_confirmation = "Ordern skapades utan en giltig rabattkod!";
        }
    }


    // Hämta information från databasen baserat på den specifika ordern
    $stmt = $connection->prepare("SELECT order_items.*, products.id as product_id, products.name as product_name, products.price as product_price, images.img_path as product_image, orders.total_amount as order_total_amount
    FROM order_items
    INNER JOIN products ON order_items.product_id = products.id
    INNER JOIN orders ON order_items.order_id = orders.order_id
    INNER JOIN images ON products.img_id = images.img_id
    WHERE order_items.order_id = ?");

    // Binda parameter
    $stmt->bind_param("i", $order_id);

    // Utför frågan
    $stmt->execute();

    // Hämta resultatet
    $result_order_items = $stmt->get_result();

    // Kontrollera om det finns rader i resultatuppsättningen
    if ($result_order_items->num_rows > 0) {
        // Bearbeta resultatet
        while ($order_item = $result_order_items->fetch_assoc()) {
        }
    } else {
        echo 'Vänligen välj minst en produkt innan du skickar din beställning !';
    }
}





?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kundsidan</title>
    <style>
        .confirmation {
            color: green;
        }

        .error {
            color: red;
        }

        table {
            border-collapse: collapse;
            width: 100%;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #f2f2f2;
        }

        .order-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px;
            overflow: hidden;
            background-color: #f9f9f9;
        }

        .order-item {
            border: 1px solid #ddd;
            padding: 10px;
            margin: 10px;
            overflow: hidden;
            background-color: #f9f9f9;
            font-family: 'Arial', sans-serif;
            align-items: center;
            justify-content: center;
            width: 40%;
        }

        .order-item img {
            float: left;
            margin-right: 10px;
            width: 100px;
            height: auto;
        }

        .order-item p {
            margin: 0;
            color: #333;
            font-size: 16px;
        }

        .order-item h4 {
            margin-top: 0;
            color: #008080;
            font-size: 20px;
        }
    </style>
</head>

<body>

    <h2>Välkommen till Kundsidan</h2>

    <form action="" method="post">
        <!-- Visa produkter med checkboxar -->
        <div style="display: flex; flex-wrap: wrap;">
            <?php
            // Select function med hjälp av checkbox från images och product för att hämta data  
            $query_get_products = "SELECT products.id, products.name, products.description, products.price, images.img_path FROM products
            INNER JOIN images ON products.img_id = images.img_id";
            $result_get_products = $connection->query($query_get_products);


            if ($result_get_products === false) {
                die('Error in query: ' . $connection->error);
            }

            // Skapar en array $products och fyller den med resultatet av SQL-frågan som utfördes tidigare.
            $products = [];
            if ($result_get_products->num_rows > 0) {
                while ($row = $result_get_products->fetch_assoc()) {
                    $products[] = $row;
                }
            } else {
                echo 'No products found.';
            }

            // visa på browser producten som är check in
            foreach ($products as $row) {
                echo '<div style="border: 1px solid #ccc; padding: 10px; margin: 10px; float: left; width: 300px;">';
                echo '<input type="checkbox" name="selected_products[]" value="' . $row['id'] . '"> ' . $row['name'];
                echo '<p>' . $row['description'] . '</p>';
                echo '<p>Price: ' . number_format($row['price'], 2, ',', ' ') . ' SEK</p>';
                echo '<img src="' . $row['img_path'] . '" alt="' . $row['name'] . '" style="width: 100px;">';
                echo '</div>';
            }

            // Visar bekräftelse
            if (isset($order_confirmation)) {
                echo '<p class="confirmation">' . $order_confirmation . '</p>';
            }






            ?>
        </div>

        </div>

        <!-- Kunduppgifter -->
        <label for="first_name">Förnamn:</label>
        <input type="text" name="first_name" required><br>

        <label for="last_name">Efternamn:</label>
        <input type="text" name="last_name" required><br>

        <label for="personal_number">Personnummer:</label>
        <input type="text" name="personal_number" required><br>

        <label for="phone">Telefon:</label>
        <input type="text" name="phone" required><br>

        <label for="address">Adress:</label>
        <input type="text" name="address" required><br>

        <label for="postal_code">Postnummer:</label>
        <input type="text" name="postal_code" required><br>

        <label for="city">Stad:</label>
        <input type="text" name="city" required><br>

        <label for="email">E-post:</label>
        <input type="email" name="email" required><br>


        <label for="discount_code">Rabattkod:</label>
        <input type="text" name="discount_code">
        <label for="shipping_option">Välj fraktalternativ:</label>
        <select name="shipping_option">
            <?php
            // Hämta fraktmöjligheterna från databasen
            $query_shipping_options = "SELECT * FROM shipping_options";
            $result_shipping_options = $connection->query($query_shipping_options);

            // Visa fraktmöjligheterna som option i select-elementet
            while ($shipping_option = $result_shipping_options->fetch_assoc()) {
                echo '<option value="' . $shipping_option['option_id'] . '">';
                echo $shipping_option['option_name'] . ' - ' . number_format($shipping_option['option_price'], 2, ',', ' ') . ' SEK';
                echo '</option>';
            }
            ?>
        </select>




        <input type="submit" name="submit_order" value="Skicka beställning">
    </form>


    <div class="order-items-container">
        <?php
        // Hämta fraktoptionsalternativ från databasen
        $query_shipping_options = "SELECT * FROM shipping_options";
        $result_shipping_options = $connection->query($query_shipping_options);

        // Förberedd fråga
        $stmt = $connection->prepare($query_order_items);
        $stmt->execute();
        $stmt_result = $stmt->get_result();

        // Kontrollera om det finns rader i resultatuppsättningen
        if ($stmt_result->num_rows > 0) {
            echo 'Ordern är skapad!</h3>';

            // Bearbeta resultatet
            while ($order_item = $result_order_items->fetch_assoc()) {
                echo '<div class="order-item">';
                echo 'Produkt: ' . htmlspecialchars($order_item['product_name']) . '<br>';
                echo 'Pris: ' . number_format($order_item['product_price'], 2, ',', ' ') . ' SEK<br>';
                echo 'Antal: ' . htmlspecialchars($order_item['quantity']) . '<br>';
                echo '<img src="' . $order_item['product_image'] . '" alt="' . $order_item['product_name'] . '" style="width: 100px;">';
                echo '</div>';
            }
        } else {
            echo 'Vänligen välj minst en produkt innan du skickar din beställning..';
        }

        ?>
    </div>
    <br>
    <br>
    <br>
    <br>
    <hr>
    <br>
    <br>
    <br>
    <br>
    <h2>Välkommen till Adminsidan</h2>

    <?php

    /*ADMINSIDAN PHP*/



    // Updatering av status på order
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["update_status"])) {
        $update_order_id = $connection->real_escape_string($_POST["update_order"]);
        $new_status = $connection->real_escape_string($_POST["new_status"]);

        // Updatering order status
        $query_update_status = "UPDATE orders SET status = '$new_status' WHERE order_id = $update_order_id";
        $result_update_status = $connection->query($query_update_status);

        if ($result_update_status) {
            echo "<p style='color: green;'>Status för order id $update_order_id har uppdaterats.</p>";
        } else {
            echo "<p style='color: red;'>Fel vid uppdatering av status för order: " . $connection->error . "</p>";
        }
    }


    /// att ta bort order från orderlistan
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["delete_order"])) {
        $delete_order_id = $connection->real_escape_string($_POST["delete_order"]);


        $query_delete_order_items = "DELETE FROM order_items WHERE order_id = $delete_order_id";
        $result_delete_order_items = $connection->query($query_delete_order_items);


        $query_delete_order = "DELETE FROM orders WHERE order_id = $delete_order_id";
        $result_delete_order = $connection->query($query_delete_order);
        //meddelande för bekräftelse om order är ta bort eller inte
        if ($result_delete_order && $result_delete_order_items) {
            echo "<p style='color: blue;'>Order id $delete_order_id har tagits bort.</p>";
        } else {
            echo "<p style='color: red;'>Fel vid borttagning av order: " . $connection->error . "</p>";
        }
    }

    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["add_discount_code"])) {
        // Hämta rabattkoden och rabattprocenten från formuläret
        $code = $connection->real_escape_string($_POST["code"]);
        $amount = $connection->real_escape_string($_POST["amount"]);

        // Lägg till rabattkoden i discount_codes-tabellen
        $query_add_discount_code = "INSERT INTO discount_codes (code, amount) VALUES ('$code', $amount)";
        $result_add_discount_code = $connection->query($query_add_discount_code);

        if ($result_add_discount_code) {
            echo "Rabattkod har lagts till framgångsrikt.";
        } else {
            echo "Fel vid läggning av rabattkod: " . $connection->error;
        }
    }

    // Hantera tillägg av fraktalternativ
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Kontrollera om formuläret för att lägga till fraktalternativ har skickats
        if (isset($_POST['add_shipping'])) {
            // Hämta data från formuläret
            $option_name = $_POST['option_name'];
            $option_price = $_POST['option_price'];

            // Skydda mot SQL-injektioner genom att använda förberedda frågor
            $query_add_shipping = $connection->prepare("INSERT INTO shipping_options (option_name, option_price) VALUES (?, ?)");
            $query_add_shipping->bind_param("sd", $option_name, $option_price);

            // Utför frågan och kontrollera om det lyckades
            if ($query_add_shipping->execute()) {
                echo "Fraktalternativ tillagt framgångsrikt";
            } else {
                echo "Ett fel uppstod vid tillägg av fraktalternativ: " . $query_add_shipping->error;
            }

            // Stäng förberedningen
            $query_add_shipping->close();
        }
    }




    // order listan ska visas i datum ordning
    $query_admin_orders = "SELECT
    orders.order_id,
    customers.first_name,
    customers.last_name,
    customers.email,
    customers.phone,
    customers.address,
    customers.postal_code,
    customers.city,
    GROUP_CONCAT(CONCAT(products.name, ' (Product-ID: ', products.id, ')') SEPARATOR ', ') as ordered_products,
    GROUP_CONCAT(order_items.order_item_id SEPARATOR ', ') as order_items_id,
    orders.total_amount,
    orders.status,
    orders.date,
    shipping_options.option_price as shipping_price
  FROM orders
  INNER JOIN customers ON orders.customer_id = customers.customer_id
  LEFT JOIN order_items ON orders.order_id = order_items.order_id
  LEFT JOIN products ON order_items.product_name = products.name
  LEFT JOIN shipping_options ON orders.shipping_option_id = shipping_options.option_id
  GROUP BY orders.order_id, customers.first_name, customers.last_name, customers.email, customers.phone, customers.address, customers.postal_code, customers.city, orders.total_amount, orders.status, orders.date, shipping_options.option_price
  ORDER BY orders.date DESC;
  ";

    $result_admin_orders = $connection->query($query_admin_orders);
    if ($result_admin_orders->num_rows > 0) {
        echo '<table>';
        echo '<tr>';
        echo '<th>Order ID</th>';
        echo '<th>Kundnamn</th>';
        echo '<th>Email</th>';
        echo '<th>Telefon</th>';
        echo '<th>Adress</th>';
        echo '<th>Beställda produkter</th>';
        echo '<th>Totalt belopp</th>';
        echo '<th>Fraktalternativ</th>';
        echo '<th>Status</th>';
        echo '<th>Datum</th>';
        echo '<th>Åtgärd</th>';
        echo '</tr>';

        while ($row = $result_admin_orders->fetch_assoc()) {
            echo '<tr>';
            echo '<td>' . $row['order_id'] . '</td>';
            echo '<td>' . $row['first_name'] . ' ' . $row['last_name'] . '</td>';
            echo '<td>' . $row['email'] . '</td>';
            echo '<td>' . $row['phone'] . '</td>';
            echo '<td>' . $row['address'] . ', ' . $row['postal_code'] . ' ' . $row['city'] . '</td>';
            echo '<td>' . $row['ordered_products'] . '</td>';
            echo '<td>' . number_format($row['total_amount'], 2, ',', ' ') . ' SEK</td>';
            echo '<td>' .  ' SEK'  . '</td>';

            echo '<td>' . $row['status'] . '</td>';
            echo '<td>' . $row['date'] . '</td>';
            echo '<td>';
            echo '<form method="post">';
            echo '<input type="hidden" name="update_order" value="' . $row['order_id'] . '">';
            echo '<select name="new_status">';
            echo '<option value="Processing">Processing</option>';
            echo '<option value="Completed">Completed</option>';
            echo '<option value="Shipped">Shipped</option>';
            echo '<option value="Canceled">Canceled</option>';
            echo '</select>';
            echo '<button type="submit" name="update_status">Uppdatera status</button>';
            echo '</form>';
            echo '<form method="post">';
            echo '<input type="hidden" name="delete_order" value="' . $row['order_id'] . '">';
            echo '<button type="submit">Ta bort</button>';
            echo '</form>';
            echo '</td>';
            echo '</tr>';
        }

        echo '</table>';
    } else {
        echo 'Inga ordrar hittades.';
    }

    ?>
    <h3>Lägg till rabattkod</h3>
    <form method="post" action="">
        <label for="code">Kod:</label>
        <input type="text" name="code" required>

        <label for="amount">Rabattprocent:</label>
        <input type="number" name="amount" required>

        <button type="submit" name="add_discount_code">Lägg till rabattkod</button>
    </form>

    <form action="" method="post">
        <label for="option_name">Fraktnamn:</label>
        <input type="text" name="option_name" required>

        <label for="option_price">Fraktpris (SEK):</label>
        <input type="number" step="0.01" name="option_price" required>

        <button type="submit" name="add_shipping">Lägg till fraktalternativ</button>
    </form>







</body>

</html>