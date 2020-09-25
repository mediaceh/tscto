# tscto

Нужно написать скрипт, который берёт с любого публичного API на ваш выбор список однотипных объектов. Условие такое, что формат ответа этого API не должен быть JSON.

За эту функциональность должен отвечать приватный метод класса.

Далее скрипт должен иметь публичный метод этого же класса, который выдает полученную ранее информацию, но уже в формате JSON.

И должен быть отдельный класс, наследник первого, с публичным методом внутри. Этот метод должен переопределять второй метод из первого класса, но возвращать данные в CSV.

Ниже в скрипте нужно написать код запуска всего этого, чтобы увидеть результат работы.
