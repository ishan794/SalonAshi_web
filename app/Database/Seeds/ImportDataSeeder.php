<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class ImportDataSeeder extends Seeder
{
    public function run()
    {
        $this->db->query('SET FOREIGN_KEY_CHECKS = 0');
        $this->db->table('service_categories')->truncate();
        $this->db->table('services')->truncate();
        $this->db->table('staff')->truncate();
        $this->db->table('staff_services')->truncate();
        $this->db->query('SET FOREIGN_KEY_CHECKS = 1');

        $categories = [
            'Gents' => [
                ['Beard Cover', 30, 'Beard trimming and coverage (base price)', 1000],
                ['Conditioner Treatment', 45, 'Nourishing conditioner treatment', 3000],
                ['Gray hair Cover', 45, 'Gray hair coverage (base price)', 3000],
                ['Hair Color', 60, 'Full hair coloring (base price)', 4500],
                ['Hair Cut (Another hair dresser)', 30, 'Standard hair styling by other stylists', 1500],
                ['Hair Cut (By Ashi)', 30, 'Professional hair styling by Ashi', 3500],
                ['Hair mask', 45, 'Nourishing hair mask treatment', 3000],
                ['Hair setting', 20, 'Professional hair setting and styling', 2500],
                ['Oil Head massage', 30, 'Relaxing head massage with oil', 1500],
                ['Oil Treatment', 45, 'Hair oil conditioning treatment', 3000]
            ],
            'Ladies' => [
                ['Conditioner Treatment', 45, 'Conditioner care treatment', 4000],
                ['Eyebrow threading', 15, 'Eyebrow shaping and threading', 300],
                ['Full face threading', 30, 'Complete facial hair threading', 1000],
                ['Hair Cut (Another hair Dresser)', 45, 'Standard hair styling by other stylists', 3500],
                ['Hair Cut (By Ashi)', 45, 'Professional hair styling by Ashi', 4500],
                ['Hair mask', 45, 'Nourishing hair mask', 4000],
                ['Keratin Treatment', 150, 'Nourishing keratin treatment (base price)', 30000],
                ['Normal Dressing', 45, 'Standard hair dressing', 3500],
                ['Oil treatment', 45, 'Nourishing oil treatment', 4000],
                ['Rebonding', 180, 'Permanent hair rebonding (base price)', 20000],
                ['Relaxing', 120, 'Hair relaxing treatment (base price)', 20000],
                ['Straightening', 120, 'Hair straightening (base price)', 20000],
                ['Upper lip & chin threading', 20, 'Upper lip and chin threading', 500]
            ],
            'Nails' => [
                ['Acrylic nail Extention', 90, 'Premium acrylic nail extensions', 6000],
                ['Gel Arts', 45, 'Gel nail art design', 3000],
                ['Gel Color', 45, 'Durable gel coloring', 2000],
                ['Gel color removal', 30, 'Gel polish removal service', 1000],
                ['Gel nail Extention', 90, 'Premium gel nail extensions', 7000],
                ['Normal Color', 30, 'Standard nail coloring', 1500]
            ],
            'Ladies Wax' => [
                ['Arms', 30, 'Arms waxing', 1000],
                ['Full Body wax', 120, 'Full body waxing', 10000],
                ['Full hand', 45, 'Full hands waxing', 3000],
                ['Full Legs wax', 45, 'Full legs waxing', 3000],
                ['Half hand', 30, 'Half hands waxing', 2500],
                ['Half Legs wax', 30, 'Half legs waxing', 2500]
            ],
            'Unisex' => [
                ['Braiding Hair', 30, 'Hair braiding style (base price)', 600],
                ['Golden facial', 60, 'Premium Golden facial treatment', 5000],
                ['Hydra facial', 90, 'Advanced Hydra facial treatment', 12000],
                ['Jelly Pedicure', 60, 'Premium jelly pedicure treatment', 4500],
                ['Manicure', 45, 'Professional manicure service', 3000],
                ['Pedicure', 45, 'Professional pedicure service', 3500],
                ['Vitamin C facial', 60, 'Nourishing Vitamin C facial treatment', 4000]
            ],
            'Grooming Packages' => [
                ['Hair cut / Gold facial', 90, 'Gents haircut + gold facial package', 11000],
                ['Hair cut / Gold facial / Setting Touch-up / Dressing', 150, 'Complete haircut, gold facial, touch-up and dressing package', 24000],
                ['Hair cut / Setting / Touch-up / Dressing', 120, 'Haircut, hair setting, touch-up and dressing package', 19000],
                ['Hair cut / Setting / Touch-up Gold Facial / Dressing / Manicure Pedicure', 210, 'Premium grooming package with haircut, setting, facial, dressing, manicure and pedicure', 30500],
                ['Pre-shoot Package', 120, 'Groom pre-shoot package', 10000]
            ],
            'Bridal Packages' => [
                ['Bridal Package (Makeup / Hair / Dressing)', 240, 'Complete bridal package with makeup, hair styling and dressing', 60000]
            ]
        ];

        $sort = 1;
        $serviceIds = [];

        foreach ($categories as $catName => $services) {
            $this->db->table('service_categories')->insert([
                'name' => $catName,
                'sort_order' => $sort++,
                'is_active' => 1
            ]);
            $catId = $this->db->insertID();

            foreach ($services as $srv) {
                $this->db->table('services')->insert([
                    'category_id' => $catId,
                    'name' => $srv[0],
                    'description' => $srv[2],
                    'duration_min' => $srv[1],
                    'price' => $srv[3],
                    'tax_pct' => 0.00,
                    'is_active' => 1
                ]);
                $serviceIds[] = $this->db->insertID();
            }
        }

        $staffMembers = [
            ['full_name' => 'ashi by', 'role' => 'Owner'],
            ['full_name' => 'M.A.Shehan Rashmitha[Rash]', 'role' => 'Stylist'],
            ['full_name' => 'Sadew', 'role' => 'Stylist'],
            ['full_name' => 'W.S.D.Sithumini(Hashi)', 'role' => 'Main Hair desining(female)']
        ];

        foreach ($staffMembers as $staff) {
            $this->db->table('staff')->insert([
                'full_name' => $staff['full_name'],
                'role' => $staff['role'],
                'is_active' => 1,
                'working_hours' => json_encode([
                    '1' => ['09:00-18:00'],
                    '2' => ['09:00-18:00'],
                    '3' => ['09:00-18:00'],
                    '4' => ['09:00-18:00'],
                    '5' => ['09:00-18:00'],
                    '6' => ['09:00-18:00'],
                    '0' => []
                ])
            ]);
            $staffId = $this->db->insertID();

            // Assign all services to the staff
            foreach ($serviceIds as $sid) {
                $this->db->table('staff_services')->insert([
                    'staff_id' => $staffId,
                    'service_id' => $sid
                ]);
            }
        }
    }
}
