
<?php

class Application
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function create(
        int $userId,
        int $excursionId,
        string $date,
        int $participants,
        int $paymentMethodId
    ): array {
        try {
            $this->db->beginTransaction();

            // Получаем цену экскурсии
            $stmt = $this->db->prepare(
                'SELECT price_per_person
                 FROM excursions
                 WHERE id = :id'
            );

            $stmt->execute(['id' => $excursionId]);
            $excursion = $stmt->fetch();

            if (!$excursion) {
                throw new RuntimeException('Выбранная экскурсия не найдена.');
            }

            // Получаем вместимость на выбранную дату
            $stmt = $this->db->prepare(
                'SELECT capacity
                 FROM excursion_dates
                 WHERE excursion_id = :excursion_id
                   AND excursion_date = :date'
            );

            $stmt->execute([
                'excursion_id' => $excursionId,
                'date' => $date
            ]);

            $schedule = $stmt->fetch();

            if (!$schedule) {
                throw new RuntimeException('На выбранную дату экскурсия не запланирована.');
            }

            // Считаем уже забронированные места
            $stmt = $this->db->prepare(
                "SELECT COALESCE(SUM(participants), 0)
                 FROM applications
                 WHERE excursion_id = :excursion_id
                   AND excursion_date = :date
                   AND status_id IN (
                       SELECT id
                       FROM application_statuses
                       WHERE name IN ('Новая', 'Подтверждена')
                   )"
            );

            $stmt->execute([
                'excursion_id' => $excursionId,
                'date' => $date
            ]);

            $booked = (int) $stmt->fetchColumn();

            if ($booked + $participants > (int) $schedule['capacity']) {
                throw new RuntimeException('Недостаточно свободных мест на выбранную дату.');
            }

            // Проверяем способ оплаты
            $stmt = $this->db->prepare(
                'SELECT id FROM payment_methods WHERE id = :id'
            );

            $stmt->execute(['id' => $paymentMethodId]);

            if (!$stmt->fetch()) {
                throw new RuntimeException('Выберите доступный способ оплаты.');
            }

            // Получаем статус новой заявки
            $stmt = $this->db->prepare(
                'SELECT id FROM application_statuses WHERE name = :name'
            );

            $stmt->execute(['name' => 'Новая']);
            $status = $stmt->fetch();

            if (!$status) {
                throw new RuntimeException('Не найден статус «Новая».');
            }

            // Рассчитываем итоговую стоимость на сервере
            $totalPrice = (float) $excursion['price_per_person'] * $participants;

            // Сохраняем заявку
            $stmt = $this->db->prepare(
                'INSERT INTO applications
                    (user_id, excursion_id, excursion_date, participants,
                     total_price, payment_method_id, status_id)
                 VALUES
                    (:user_id, :excursion_id, :date, :participants,
                     :total_price, :payment_method_id, :status_id)'
            );

            $stmt->execute([
                'user_id' => $userId,
                'excursion_id' => $excursionId,
                'date' => $date,
                'participants' => $participants,
                'total_price' => $totalPrice,
                'payment_method_id' => $paymentMethodId,
                'status_id' => $status['id']
            ]);

            $this->db->commit();

            return [
                'success' => true,
                'message' => 'Заявка успешно отправлена!'
            ];

        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
}
