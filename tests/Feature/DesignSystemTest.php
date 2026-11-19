<?php

namespace Tests\Feature;

use Tests\TestCase;

class DesignSystemTest extends TestCase
{
    public function test_design_system_reference_is_available(): void
    {
        $this->get('/design-system')
            ->assertOk()
            ->assertSee('Design system')
            ->assertSee('Warna brand')
            ->assertSee('Tipografi')
            ->assertSee('Komponen')
            ->assertSee('#183E8C');
    }
}
