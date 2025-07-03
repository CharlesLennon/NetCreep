<?php

namespace App\Livewire;

use Livewire\Component;
use Livewire\Attributes\On; 


class MonacoEditor extends Component
{

    public $class, $field, $id, $language, $returnUrl;
    public $content;

    public function mount($class, $field, $id, $language = 'blade', string $returnUrl = "/")
    {
        $this->class = $class;
        $this->field = $field;
        $this->id = $id;
        $this->language = $language;
        
        $this->content = $this->class::find($this->id)->{$this->field} ?? 'ERROR: Content not found';
        $this->content = escapeQuotes($this->content);
    }    

    #[On('save')]
    public function save($content)
    {
        $this->content = $content;
        $model = $this->class::find($this->id);
        if ($model) {
            $model->{$this->field} = $this->content;
            $model->save();
        }
        session()->flash('status', 'Successfully updated.');

        $this->redirect($this->returnUrl);
    }
   
}
