import pyautogui
import time
import keyboard
from pynput import mouse

# Глобальные переменные для хранения координат
click_x, click_y = -1, -1
point_selected = False

def on_click(x, y, button, pressed):
    """Обработчик кликов мыши для выбора точки"""
    global click_x, click_y, point_selected
    if pressed and button == mouse.Button.left:
        click_x, click_y = x, y
        point_selected = True
        print(f"\nТочка выбрана: ({x}, {y})")
        return False  # Останавливаем слушатель

def select_point():
    """Функция для выбора точки на экране"""
    global point_selected
    print("Пожалуйста, кликните мышкой в нужную точку экрана...")
    
    # Создаем слушателя мыши
    listener = mouse.Listener(on_click=on_click)
    listener.start()
    listener.join()  # Ждем пока не будет сделан клик
    
    return click_x, click_y

def auto_clicker(x, y, interval_sec, clicks_count=0):
    """Автокликер с безопасным выходом"""
    try:
        print("\nАвтокликер запущен. Для остановки нажмите Ctrl+C")
        print(f"Цель: ({x}, {y}) | Интервал: {interval_sec} сек.")
        
        counter = 0
        while True:
            if clicks_count > 0 and counter >= clicks_count:
                break
                
            # Сохраняем текущую позицию мыши
            original_pos = pyautogui.position()
            
            # Выполняем клик
            pyautogui.click(x, y)
            counter += 1
            print(f"Клик #{counter} выполнен в ({x}, {y})")
            
            # Возвращаем мышь на исходную позицию
            pyautogui.moveTo(original_pos)
            
            # Ожидание с возможностью прерывания
            time.sleep(interval_sec)
            
    except KeyboardInterrupt:
        print("\nАвтокликер остановлен пользователем")

if __name__ == "__main__":
    try:
        # Выбор точки на экране
        x, y = select_point()
        
        if not point_selected:
            print("Ошибка: точка не выбрана")
            exit()
        
        # Ввод параметров
        interval = float(input("Введите интервал между кликами (секунды): "))
        count = int(input("Введите количество кликов (0 = бесконечно): "))
        
        # Обратный отсчет перед запуском
        print("\nЗапуск через 3 секунды... Переключитесь в нужное окно!")
        for i in range(3, 0, -1):
            print(f"{i}...")
            time.sleep(1)
        
        # Запуск автокликера
        auto_clicker(x, y, interval, count)
        
    except ValueError:
        print("Ошибка: некорректный ввод числа")
    except Exception as e:
        print(f"Произошла ошибка: {str(e)}")