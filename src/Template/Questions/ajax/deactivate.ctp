<?php
echo $this->Jquery->ajaxLink(__('Activate'), ['url' => ['action' => 'activate', 'question' => $question->id]]);
