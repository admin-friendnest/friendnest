# FriendNest

**Reignite Genuine Social Connection.**  
FriendNest is a lightweight open-source social network that brings back the authenticity of early social media — where thoughts, feelings, hobbies, and status updates matter again. Built with heart, powered by people.

---

## ✨ Features

- **Familiar, Thoughtful Design**  
  A blend of Twitter and Facebook-style interactions, reimagined for meaningful connection.

- **Lightweight & Organized**  
  Data is stored in a clean and structured JSON format for simplicity and reliability.

- **Mobile Responsive**  
  FriendNest works beautifully on all screen sizes — from desktop to mobile.

- **Open Source & Customizable**  
  Fully open and forkable. Modify the platform for your niche community or large-scale use case.

- **AI-Assisted, Human-Crafted**  
  While development is accelerated with the help of AI, all customization, design, and debugging were done by real people.

---

## 🆕 Updates

### 📅 May 27, 2025  
- **🔐 Reset Password Page Added**  
  We've added a simple but effective reset password page! If users forget their password, they can now reset it — as long as the email they provide matches what's already in the system.  
  > *Note: This reset system currently checks against existing account data rather than sending email verification. It’s a fast, no-frills way to recover access — great for internal networks or prototype stages.*

---

## ⚙️ Project Setup & Important Notes

> Before running FriendNest, make sure you configure the following:

- 🔐 **Add your hCaptcha Secret Key**  
  Set your own secret key for hCaptcha in `includes/config.php` to secure the registration page.

- 👤 **Set Admin Account**  
  After creating a user account, manually set `"role": admin` in `data/users.json` for that user to enable Admin Panel access.

- 📰 **Customize News Feed**  
  Modify the RSS feed source in `fetch_news.php` to reflect the type of news you want your community to see.

- 🌍 **Set Your Timezone**  
  Update the $timezone = new DateTimeZone(''); to match your region.

- 🛠 **Make It Yours**  
  Feel free to fork and customize the project. Add new features, themes, integrations — anything you like!

---

## 📦 Storage Approach: Why JSON?

FriendNest currently uses **JSON-based storage** rather than a database like MySQL.  
This decision was made to keep the project lightweight and accessible to beginners, making it easier to:
- Read and edit data manually.
- Deploy the platform without complex setup.
- Prototype and iterate quickly.

As the project grew, JSON storage was retained to lower the barrier for contribution and testing. However, migrating to a full database system is totally supported and encouraged for larger-scale implementations.

---

## 💡 Contributing & Community

This project is built **by learners and for learners**.  
As we continue to grow, we welcome improvements from developers of all experience levels.

📢 **Note:** This project was started with assistance from AI to scaffold functionality and structure. However, design, debugging, and user experience work were manually done. We believe in transparency — and collaboration.

If you see something that can be improved or optimized, we would love your contributions! Or simply **fork the project** and make it your own.

---

## 🚀 Get Started Today

No noise. No distractions. Just real social presence.

👉 [Sign up and try it yourself](https://friendnest.infy.uk)

---

## 🔗 License

This project is open-source under the [MIT License](https://friendnest.infy.uk/license.php).  
Built for sharing. Fork freely. Improve collaboratively.
