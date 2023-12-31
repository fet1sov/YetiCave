<form class="form form--add-lot container <?php if(!empty(array_filter($error_codes, 'strlen'))): ?> form--invalid <?php endif; ?>" action="../add.php" method="post" enctype="multipart/form-data"> <!-- form--invalid -->
      <h2>Добавление лота</h2>
      <div class="form__container-two">
        <div class="form__item <?php if (!empty($error_codes["lot-name"])): ?> form__item--invalid <?php endif; ?>">
          <label for="lot-name">Наименование <sup>*</sup></label>
          <input value="<?= htmlspecialchars(get_post_val("lot-name")) ?>" id="lot-name" type="text" name="lot-name" placeholder="Введите наименование лота">
          <span class="form__error"><?= $error_codes["lot-name"] ?></span>
        </div>
        <div class="form__item <?php if (!empty($error_codes["category"])): ?> form__item--invalid <?php endif; ?>">
          <label for="category">Категория <sup>*</sup></label>
          <select value="<?= get_post_val("category") ?>" id="category" name="category">
              <option value="0" hidden>Выберите категорию</option>
            <?php foreach($categories as $item): ?>
                <option value="<?= $item['id'] ?>" <?php if (get_post_val("category") == $item['id']): ?> selected <?php endif; ?>><?= htmlspecialchars($item['name']) ?></option>
            <?php endforeach; ?>
          </select>
          <span class="form__error"><?= $error_codes["category"] ?></span>
        </div>
      </div>
      <div class="form__item form__item--wide <?php if (!empty($error_codes["message"])): ?> form__item--invalid <?php endif; ?>">
        <label for="message">Описание <sup>*</sup></label>
        <textarea id="message" name="message" placeholder="Напишите описание лота"><?= htmlspecialchars(get_post_val("message")) ?></textarea>
        <span class="form__error"><?= $error_codes["message"] ?></span>
      </div>
      <div class="form__item form__item--file <?php if (!empty($error_codes["lot-img"])): ?> form__item--invalid <?php endif; ?>">
        <label>Изображение <sup>*</sup></label>
        <div class="form__input-file">
          <input name="lot-img" class="visually-hidden" type="file" id="lot-img">
          <label for="lot-img">
            Добавить
          </label>
          <span class="form__error"><?= $error_codes["lot-img"] ?></span>
        </div>
      </div>
      <div class="form__container-three">
        <div class="form__item form__item--small <?php if (!empty($error_codes["lot-rate"])): ?> form__item--invalid <?php endif; ?>">
          <label for="lot-rate">Начальная цена <sup>*</sup></label>
          <input value="<?= htmlspecialchars(get_post_val("lot-rate")) ?>" id="lot-rate" type="text" name="lot-rate" placeholder="0">
          <span class="form__error"><?= $error_codes["lot-rate"] ?></span>
        </div>
        <div class="form__item form__item--small <?php if (!empty(($error_codes["lot-step"]))): ?> form__item--invalid <?php endif; ?>">
          <label for="lot-step">Шаг ставки <sup>*</sup></label>
          <input value="<?= htmlspecialchars(get_post_val("lot-step")) ?>" id="lot-step" type="text" name="lot-step" placeholder="0">
          <span class="form__error"><?= $error_codes["lot-step"] ?></span>
        </div>
        <div class="form__item <?php if (!empty($error_codes["lot-date"])): ?> form__item--invalid <?php endif; ?>">
          <label for="lot-date">Дата окончания торгов <sup>*</sup></label>
          <input value="<?= htmlspecialchars(get_post_val("lot-date")) ?>" class="form__input-date" id="lot-date" type="text" name="lot-date" placeholder="Введите дату в формате ГГГГ-ММ-ДД">
          <span class="form__error"><?= $error_codes["lot-date"] ?></span>
        </div>
      </div>
      <span class="form__error form__error--bottom">Пожалуйста, исправьте ошибки в форме.</span>
      <button type="submit" class="button">Добавить лот</button>
    </form>
  </main>