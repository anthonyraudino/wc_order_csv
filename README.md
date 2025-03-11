# WooCommerce Order CSV Export Plugin
Easily export WooCommerce orders as CSV for accounting and inventory management.

---

## 🚀 Features
- 📥 **Download orders as CSV** with SKU, product name, prices, quantity, and barcode.
- 🔒 **Secure access** using nonces and user authentication.
- 🎯 **Easy integration**—adds a "Download CSV" button to order details pages.
- 📄 **Custom meta support** (supports `_global_unique_id` and `wcwp_wholesale` custom fields).
- ⚡ **Lightweight & Fast**—no bloat, just pure functionality.

---

## 🔧 Installation
### **1. Upload & Activate**
- Download the latest release from [GitHub](https://github.com/anthonyraudino/wc_order_csv).
- Upload it to **`wp-content/plugins/`** in your WordPress installation.
- Go to **WordPress Admin > Plugins**, find "WooCommerce Order CSV Export" and click **Activate**.

### **2. Install via WordPress**
- Go to **Plugins > Add New** in your WordPress dashboard.
- Click **Upload Plugin** and select the `.zip` file.
- Install & Activate.

---

## 📌 Usage
1. **Go to "My Account > Orders".**
2. Open any **Completed Order**.
3. Click the **"Download CSV"** button.
4. The CSV file will be generated and downloaded.

📌 *The CSV includes:*  
✅ SKU  
✅ Product Name  
✅ Regular Price  
✅ Wholesale Price (Custom Meta)  
✅ Quantity  
✅ Barcode (Custom Meta)

Future updates are planned to add customisation to the exported CSV based on custom fields.

---

## 🔒 Security & Permissions
- Only the **logged-in customer** who placed the order can download the CSV.
- Uses **WordPress nonces** to prevent unauthorized access.

---

## 🛠️ Customization
- Modify which fields appear in the CSV by editing the `generate_csv()` method in `woocommerce-order-csv-export.php`.

---

## 🐛 Issues & Feature Requests
Have a bug or a feature request? Open an issue on [GitHub](https://github.com/anthonyraudino/wc_order_csv/issues).

---

## 📜 License
This project is licensed under the **GPL-2.0 License**.

---

## ⭐ Like this plugin?
Consider giving it a ⭐ on [GitHub](https://github.com/anthonyraudino/wc_order_csv)! 🚀

---

### **🔗 Links**
- 🛍️ **WooCommerce**: [https://woocommerce.com/](https://woocommerce.com/)
- 🔥 **Anthony Raudino**: [https://anthonyraudino.com/](https://anthonyraudino.com/)
