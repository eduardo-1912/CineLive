<?php

namespace common\components;

use Yii;
use yii\base\Widget;

class ToastWidget extends Widget
{
    public function run()
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();

        if (empty($flashes)) {
            return '';
        }

        $output = '<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 2000;">';

        foreach ($flashes as $type => $messages) {
            foreach ((array) $messages as $message) {
                // Traduz tipo para classes Bootstrap
                switch ($type) {
                    case 'success':
                        $class = 'text-bg-success';
                        $btnClass = 'btn-close-white';
                        break;
                    case 'error':
                        $class = 'text-bg-danger';
                        $btnClass = 'btn-close-white';
                        break;
                    case 'warning':
                        $class = 'text-bg-warning';
                        $btnClass = '';
                        break;
                    default:
                        $class = 'text-bg-info';
                        $btnClass = '';
                        break;
                }

                $output .= <<<HTML
                <div class="p-1 rounded-3 toast align-items-center shadow-sm {$class}" role="alert" aria-live="assertive" aria-atomic="true">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="toast-body">
                      {$message}
                    </div>
                    <button type="button" class="btn-close {$btnClass} me-3 my-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                  </div>
                </div>
                HTML;
            }
        }

        $output .= '</div>';

        // Script JS para mostrar automaticamente todos os toasts
        $js = <<<JS
            document.querySelectorAll('.toast').forEach(toastEl => {
              const toast = new bootstrap.Toast(toastEl, { delay: 6000 , animation: true });
              toast.show();
            });
            JS;
        $this->view->registerJs($js);
        return $output;
    }
}
