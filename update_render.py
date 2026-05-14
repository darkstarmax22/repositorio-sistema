import os
import re

livewire_classes_dir = r"c:\xampp\htdocs\repositorio\sistema\app\Livewire"

for filename in os.listdir(livewire_classes_dir):
    if not filename.endswith(".php"):
        continue

    filepath = os.path.join(livewire_classes_dir, filename)
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # check if public function with() exists
    if 'public function with()' in content or 'public function with(' in content:
        # replace render method
        component_name = re.search(r"return view\('livewire\.(.*?)'\);", content)
        if component_name:
            comp_name = component_name.group(1)
            new_render = f"    public function render()\n    {{\n        return view('livewire.{comp_name}', $this->with());\n    }}"
            content = re.sub(r"    public function render\(\)\n    \{\n        return view\('livewire\..*?'\);\n    \}", new_render, content)
            
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(content)
            print(f"Updated render method in {filename}")
