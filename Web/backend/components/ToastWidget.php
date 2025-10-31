<?php

namespace backend\components;

use Yii;
use yii\base\Widget;

class ToastWidget extends Widget
{
    public function run()
    {
        $session = Yii::$app->session;
        $flashes = $session->getAllFlashes();

        if (empty($flashes)) {
            return ''; // Não mostrar nada se não houver mensagens
        }

        $output = '<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index: 2000;">';

        foreach ($flashes as $type => $messages) {
            foreach ((array) $messages as $message) {
                // Traduz tipo para classes Bootstrap
                switch ($type) {
                    case 'success':
                        $class = 'text-bg-success';
                        break;
                    case 'error':
                        $class = 'text-bg-danger';
                        break;
                    case 'warning':
                        $class = 'text-bg-warning';
                        break;
                    default:
                        $class = 'text-bg-info';
                        break;
                }

                $output .= <<<HTML
                <div class="toast align-items-center {$class}" role="alert" aria-live="assertive" aria-atomic="true">
                  <div class="d-flex justify-content-between align-items-center">
                    <div class="toast-body">
                      {$message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-3 my-auto" data-bs-dismiss="toast" aria-label="Fechar"></button>
                  </div>
                </div>
                HTML;
            }
        }

        $output .= '</div>';

        // Script JS para mostrar automaticamente todos os toasts
        $js = <<<JS
            document.querySelectorAll('.toast').forEach(toastEl => {
              const toast = new bootstrap.Toast(toastEl, { delay: 4000 });
              toast.show();
            });
            JS;
        $this->view->registerJs($js);
        return $output;
    }
}
