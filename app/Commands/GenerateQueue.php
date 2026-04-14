<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\ServiceModel;
use App\Models\QueueModel;

class GenerateQueue extends BaseCommand
{
    protected $group       = 'Queue';
    protected $name        = 'queue:generate';
    protected $description = 'Generate daily queue numbers for all services';

    public function run(array $params)
    {
        $tanggal = $params[0] ?? date('Y-m-d');
        
        CLI::write('Starting queue generation for date: ' . $tanggal, 'yellow');
        CLI::newLine();
        
        $serviceModel = new ServiceModel();
        $queueModel = new QueueModel();
        
        // Get all active services
        try {
            $services = $serviceModel->where('is_active', 1)->findAll();
            
            if (empty($services)) {
                CLI::error('No active services found!');
                return;
            }
            
            CLI::write('Found ' . count($services) . ' active services', 'green');
            CLI::newLine();
            
        } catch (\Exception $e) {
            CLI::error('Error fetching services: ' . $e->getMessage());
            return;
        }
        
        $db = \Config\Database::connect();
        $totalGenerated = 0;
        
        foreach ($services as $service) {
            $kodeAntrian = $service['kode_antrian'];
            $lantai = $service['lantai'];
            
            // Determine how many numbers to generate
            $count = $this->getQueueCount($kodeAntrian);
            
            CLI::write("Processing {$kodeAntrian} - {$service['nama_pelayanan']}", 'cyan');
            CLI::write("  Lantai: {$lantai}, Count: {$count}", 'white');
            
            try {
                // Check if already generated for this date
                $existing = $queueModel->where([
                    'kode_antrian' => $kodeAntrian,
                    'tanggal' => $tanggal
                ])->countAllResults();
                
                if ($existing > 0) {
                    CLI::write("  → Skipped: {$existing} queues already exist for {$kodeAntrian}", 'yellow');
                    CLI::newLine();
                    continue;
                }
                
                // Start transaction for this service
                $db->transBegin();
                
                // Generate queue numbers
                for ($i = 1; $i <= $count; $i++) {
                    $fullNumber = $kodeAntrian . str_pad($i, 3, '0', STR_PAD_LEFT);
                    
                    $insertData = [
                        'kode_antrian' => $kodeAntrian,
                        'nomor_antrian' => $i,
                        'full_number' => $fullNumber,
                        'lantai' => $lantai,
                        'tanggal' => $tanggal,
                        'waktu_masuk' => date('Y-m-d H:i:s'),
                        'status' => 'waiting'
                    ];
                    
                    // Insert with error checking
                    if (!$queueModel->insert($insertData)) {
                        // Get validation errors
                        $errors = $queueModel->errors();
                        CLI::error("  Failed to insert queue {$fullNumber}");
                        CLI::error("  Errors: " . json_encode($errors));
                        $db->transRollback();
                        throw new \Exception("Insert failed for {$fullNumber}");
                    }
                }
                
                // Update counter
                try {
                    $counterModel = new \App\Models\QueueCounterModel();
                    $counterModel->updateCounter($kodeAntrian, $tanggal, $count);
                } catch (\Exception $e) {
                    CLI::write("  Warning: Counter update failed - " . $e->getMessage(), 'yellow');
                    // Continue anyway, counter is not critical
                }
                
                // Commit transaction
                if ($db->transStatus() === false) {
                    $db->transRollback();
                    CLI::error("  Transaction failed for {$kodeAntrian}");
                    CLI::error("  Database error: " . $db->error()['message']);
                    continue;
                } else {
                    $db->transCommit();
                    $totalGenerated += $count;
                    CLI::write("  → Generated {$count} queues for {$kodeAntrian}", 'green');
                }
                
            } catch (\Exception $e) {
                $db->transRollback();
                CLI::error("  Error processing {$kodeAntrian}: " . $e->getMessage());
                CLI::write("  Stack trace: " . $e->getTraceAsString(), 'red');
            }
            
            CLI::newLine();
        }
        
        CLI::write('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'cyan');
        CLI::write("✓ Successfully generated {$totalGenerated} queue numbers for {$tanggal}", 'green');
        CLI::write('━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━', 'cyan');
    }
    
    /**
     * Get queue count based on service code
     */
    private function getQueueCount($kodeAntrian)
    {
        // Special rules
        if ($kodeAntrian === 'E') {
            return 300; // LINTAS KLASTER - LAYANAN 24 JAM
        }
        
        if ($kodeAntrian === 'A') {
            return 250; // KLASTER 3 - USIA DEWASA
        }
        
        // Default for all other services
        return 200;
    }
}