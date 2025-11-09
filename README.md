<<<<<<< HEAD
# ASIMA
Sistem Penentuan Kemampuan diri berbasis AI (Chatbot)
=======
## Dokumentasi Fungsi Sistem

Struktur ini berisi file Markdown terpisah untuk setiap fungsi utama dalam sistem (PHP dan JavaScript). Setiap berkas mencakup: deskripsi singkat, parameter, nilai balik, efek samping, dan potensi error.

### PHP

- config/chatbot.php
  - handleGeneralQuestion
  - getGeminiResponse
  - searchChatHistory
  - logChatInteraction
- chatbot.php (root)
  - handleGeneralQuestion
  - handleLinkQuestion
  - getGeminiResponse
  - searchChatHistory
  - logChatInteraction
- config/test_and_recommendation.php
  - getTestQuestions
  - submitTest
  - getDominantAcademicCategory
  - generateGeminiCareerAnalysis
  - generateCareerRecommendationText
- test_and_recommendation.php (root)
  - getTestQuestions
  - submitTest
  - getDominantAcademicCategory
  - generateCareerRecommendationText
- config/test.php
  - getTestQuestions (MySQLi)
  - submitTest (MySQLi)
- config/recommendation.php
  - getDominantAcademicCategory (MySQLi)
  - generateCareerRecommendationText
- utils.php & config/utils.php
  - send_json_response
  - getScoreCategory
- user_functions.php & config/user_functions.php
  - getAcademicTranscript
  - updatePassword
  - uploadPhoto
  - updateBiodata
- chat_functions.php
  - loadConversationsFromDB
  - saveConversationToDB
  - deleteConversationFromDB

### JavaScript

- js/ui.js: fungsi UI percakapan dan tes
- js/app.js: alur aplikasi, komunikasi API, logika chat/tes
- js/visuals.js: render grafik radar
- templates: fungsi inline (profil, login)

Catatan: Beberapa fungsi ada dalam dua lokasi (root dan config). Dokumentasi per fungsi menyebutkan semua lokasi terkait.
>>>>>>> c530372 (ASIMA)
