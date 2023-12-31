<section class="lot-item container">
      <h2><?= $lot_info['name'] ?></h2>
      <div class="lot-item__content">
        <div class="lot-item__left">
          <div class="lot-item__image">
            <img src="<?= $lot_info['image_link'] ?>" width="730" height="548" alt="<?= htmlspecialchars($lot_info['category_name']) ?>">
          </div>
          <p class="lot-item__category">Категория: <span><?= $lot_info['category_name'] ?> </span></p>
          <p class="lot-item__description"><?= htmlspecialchars($lot_info['description']) ?></p>
        </div>
        <div class="lot-item__right">
          <?php if (isset($_SESSION['is_auth']) && $_SESSION['is_auth']): ?>
            <div class="lot-item__state">
              <?php
                  $date = get_dt_range($lot_info['expire_date']);
                  $hours = $date[0];
                  $minutes = $date[1];
              ?>

              <?php if(strtotime('now') <= strtotime($lot_info['expire_date']) && (isset($_SESSION['user_id']) && $lot_info['author_id'] !== $_SESSION['user_id'])): ?>
                <div class="lot-item__timer timer <?php if (intval($hours) < 24): ?> timer--finishing <?php endif; ?>">
                    <?= sprintf('%s:%s', $hours, $minutes) ?>
                </div>
              <?php endif; ?>

              <div class="lot-item__cost-state">
                <div class="lot-item__rate">
                  <span class="lot-item__amount">Текущая цена</span>
                  <span class="lot-item__cost"><?= make_number($price) ?></span>
                </div>
                <div class="lot-item__min-cost">
                  Мин. ставка <span><?= make_number($min_bet) ?></span>
                </div>
              </div>

              <?php if(strtotime('now') <= strtotime($lot_info['expire_date']) && (isset($_SESSION['user_id']) && $lot_info['author_id'] !== $_SESSION['user_id'])): ?>
                <?php if(!empty($bet_history) && $bet_history[0]['user_id'] !== $_SESSION['user_id'] || empty($bet_history)): ?>
                  <form class="lot-item__form" action="lot.php?id=<?= $lot_info["id"] ?>" method="post" autocomplete="off">
                    <p class="lot-item__form-item form__item <?php if(!empty($error)): ?> form__item--invalid <?php endif; ?>">
                      <label for="cost">Ваша ставка</label>
                      <input id="cost" type="text" name="cost" placeholder="<?= make_number($min_bet) ?>">
                      <span class="form__error"><?= $error ?></span>
                    </p>
                    <button type="submit" class="button">Сделать ставку</button>
                  </form>
                <?php endif; ?>
              <?php endif; ?>
              
              
          </div>
          <?php endif; ?>

          <div class="history">
            <h3>История ставок (<span><?= count($bet_history) ?></span>)</h3>
            <table class="history__list">
              <?php foreach($bet_history as $bet): ?>
                  <tr class="history__item">
                    <td class="history__name"><?= $bet["account_name"] ?></td>
                    <td class="history__price"><?= pretty_number($bet["summary"]) ?> р</td>
                    <td class="history__time"><?= format_time($bet["create_date"]) ?></td>
                  </tr>
              <?php endforeach; ?>
              
            </table>
          </div>
        </div>
      </div>
    </section>
  </main>