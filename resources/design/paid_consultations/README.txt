PAID CONSULTATIONS DESIGN SOURCE
================================

Тази папка съдържа ОФИЦИАЛНИЯ дизайн за модула "Онлайн консултации".

ВАЖНО:
Това НЕ е референция.
Това НЕ е inspiration.
Това е SOURCE OF TRUTH.

Cursor и разработката трябва да следват ТОЧНО тези ресурси.
Никаква импровизация, никакъв redesign, никакви допълнителни UI решения.


--------------------------------------------------
СТРУКТУРА
--------------------------------------------------

00_master/
- landing_page_master.png
→ Основният екран на страницата за консултации.
→ Това е финалният layout, който трябва да се възпроизведе 1:1.

01_components/
- duration_toggle_30_60.png
→ UI компонент за избор на продължителност (30/60 мин).
→ Да НЕ се заменя с custom решение.

02_icons/
- icon_phone.svg
- icon_chat.svg
- icon_written.svg
- icon_video_viber.svg
→ Иконите са ФИКСИРАНИ.
→ Не се редактират, не се заменят.

03_background/
- landing_background.webp
→ Фонът за hero секцията.

04_payment/
- borica_payment_screen.png
→ Референция за платежен екран (по-късен етап).

05_success/
- generic_success_page.png
- chat_success_page.png
→ Екрани след успешна консултация.

06_chat_states/
- chat_state_waiting.png
- chat_state_active.png
- chat_state_completed.png
→ Състояния на чат сесията.
→ Това са state screens, не decoration.

07_phone_flow/
- phone_step_1.png
- phone_step_2.png
- phone_step_3.png

08_written_flow/
- written_step_1.png
- written_step_2.png
- written_step_3.png

09_chat_flow/
- chat_step_1.png
- chat_step_2.png
- chat_step_3.png

→ Това са flow екрани за различните типове консултации.

10_specs/
- technical_specification.docx
→ Официална логика и изисквания за системата.


--------------------------------------------------
ПРАВИЛА ЗА РАЗРАБОТКА
--------------------------------------------------

1. UI НЕ се измисля
Всичко се копира от design файловете.

2. Layout НЕ се променя
Spacing, структура, hierarchy трябва да съвпадат с master.

3. Компоненти НЕ се заменят
Toggle-и, cards, бутони → използват се както са в дизайна.

4. Икони са статични
Не се правят dynamic, не се сменят от админ.

5. Текстовете са системни
НЕ се вкарват в CMS.
НЕ се правят editable.

6. Единствено dynamic:
→ ЦЕНИТЕ (pricing)

- price_eur
- price_bgn
- price_eur_60 (video)
- price_bgn_60 (video)
- show_bgn_price

7. show_bgn_price
→ контролира дали се показва цената в лева
→ ръчно се управлява от админ
→ няма автоматична логика по дата

8. Video консултация
→ има 30 / 60 минути
→ трябва да използва design toggle, НЕ текстов ред

9. Никаква импровизация
Ако нещо липсва → пита се, не се измисля.


--------------------------------------------------
ЦЕЛ
--------------------------------------------------

Да се постигне:
→ pixel-accurate UI
→ стабилен и предвидим frontend
→ без разминаване между дизайн и код

Тази папка е единственият източник за UI решения.