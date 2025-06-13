import pyautogui
import time
import keyboard
import sys
from PyQt5.QtWidgets import QApplication, QWidget, QVBoxLayout, QHBoxLayout, QPushButton, QLabel, QSpinBox, QDoubleSpinBox

class AutoClickerApp(QWidget):
    def __init__(self):
        super().__init__()
        self.is_running = False
        self.x = 0
        self.y = 0
        self.interval = 1.0
        self.init_ui()

    def init_ui(self):
        self.setWindowTitle('Автокликер')
        self.setGeometry(300, 300, 400, 200)

        layout = QVBoxLayout()

        # Координаты
        coords_layout = QHBoxLayout()
        coords_layout.addWidget(QLabel('X:'))
        self.x_spin = QSpinBox()
        self.x_spin.setRange(0, 9999)
        self.x_spin.valueChanged.connect(self.update_x)
        coords_layout.addWidget(self.x_spin)

        coords_layout.addWidget(QLabel('Y:'))
        self.y_spin = QSpinBox()
        self.y_spin.setRange(0, 9999)
        self.y_spin.valueChanged.connect(self.update_y)
        coords_layout.addWidget(self.y_spin)
        layout.addLayout(coords_layout)

        # Интервал
        interval_layout = QHBoxLayout()
        interval_layout.addWidget(QLabel('Интервал (секунды):'))
        self.interval_spin = QDoubleSpinBox()
        self.interval_spin.setRange(0.1, 3600)
        self.interval_spin.setValue(1.0)
        self.interval_spin.setSingleStep(0.1)
        self.interval_spin.valueChanged.connect(self.update_interval)
        interval_layout.addWidget(self.interval_spin)
        layout.addLayout(interval_layout)

        # Кнопка получения текущей позиции
        get_pos_btn = QPushButton('Получить текущую позицию курсора (F6)')
        get_pos_btn.clicked.connect(self.get_cursor_position)
        layout.addWidget(get_pos_btn)

        # Статус
        self.status_label = QLabel('Статус: Остановлен')
        layout.addWidget(self.status_label)

        # Кнопки управления
        control_layout = QHBoxLayout()
        self.start_btn = QPushButton('Старт (F7)')
        self.start_btn.clicked.connect(self.start_clicking)
        control_layout.addWidget(self.start_btn)

        self.stop_btn = QPushButton('Стоп (F8)')
        self.stop_btn.clicked.connect(self.stop_clicking)
        control_layout.addWidget(self.stop_btn)
        layout.addLayout(control_layout)

        # Информация
        info_label = QLabel('Горячие клавиши:\nF6 - получить текущую позицию курсора\nF7 - старт\nF8 - стоп')
        layout.addWidget(info_label)

        self.setLayout(layout)

        # Настройка горячих клавиш
        keyboard.add_hotkey('f6', self.get_cursor_position)
        keyboard.add_hotkey('f7', self.start_clicking)
        keyboard.add_hotkey('f8', self.stop_clicking)

    def update_x(self, value):
        self.x = value

    def update_y(self, value):
        self.y = value

    def update_interval(self, value):
        self.interval = value

    def get_cursor_position(self):
        x, y = pyautogui.position()
        self.x = x
        self.y = y
        self.x_spin.setValue(x)
        self.y_spin.setValue(y)
        self.status_label.setText(f'Позиция курсора: ({x}, {y})')

    def start_clicking(self):
        if not self.is_running:
            self.is_running = True
            self.status_label.setText(f'Статус: Запущен - кликает по ({self.x}, {self.y}) каждые {self.interval} сек')
            self.clicker_thread()

    def stop_clicking(self):
        self.is_running = False
        self.status_label.setText('Статус: Остановлен')

    def clicker_thread(self):
        if not self.is_running:
            return
        
        pyautogui.click(self.x, self.y)
        QApplication.processEvents()
        time.sleep(self.interval)
        self.clicker_thread()  # Рекурсивный вызов для продолжения кликов

if __name__ == '__main__':
    app = QApplication(sys.argv)
    window = AutoClickerApp()
    window.show()
    sys.exit(app.exec_()) 