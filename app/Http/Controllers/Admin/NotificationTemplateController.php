<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use App\Models\NotificationTemplate;

class NotificationTemplateController extends Controller
{
    /**
     * List all notification templates.
     */
    public function index()
    {
        try {
            $templates = NotificationTemplate::orderBy('name')->get();
            return Inertia::render('Admin/NotificationTemplates/Index', [
                'templates' => $templates,
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationTemplateController@index error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return Inertia::render('Admin/NotificationTemplates/Index', ['templates' => []]);
        }
    }

    /**
     * Show form for creating or editing a template.
     */
    public function edit(?int $id = null)
    {
        try {
            $template = $id ? NotificationTemplate::findOrFail($id) : new NotificationTemplate();
            return Inertia::render('Admin/NotificationTemplates/Edit', [
                'template' => $template,
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationTemplateController@edit error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('admin.notification-templates.index')->with('error', 'Template not found.');
        }
    }

    /**
     * Store or update a notification template.
     */
    public function save(Request $request, ?int $id = null)
    {
        try {
            $validated = $request->validate([
                'name'       => 'required|string|max:255|unique:notification_templates,name,' . $id,
                'subject'    => 'required|string|max:500',
                'body_text'  => 'nullable|string|max:10000',
                'body_html'  => 'nullable|string|max:20000',
                'variables'  => 'nullable|json',
                'is_active'  => 'required|boolean',
            ]);

            if ($id) {
                $template = NotificationTemplate::findOrFail($id);
                $template->update($validated);
                Log::info('Notification template updated.', ['template_id' => $id]);
            } else {
                $template = NotificationTemplate::create($validated);
                Log::info('Notification template created.', ['template_id' => $template->id]);
            }

            return redirect()->route('admin.notification-templates.index')->with('success', 'Template saved.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            throw $e;
        } catch (\Throwable $e) {
            Log::error('NotificationTemplateController@save error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->withErrors(['error' => 'Save failed.'])->withInput();
        }
    }

    /**
     * Preview a template with sample data.
     */
    public function preview(Request $request)
    {
        try {
            $request->validate([
                'subject'   => 'required|string',
                'body_html' => 'nullable|string',
                'body_text' => 'nullable|string',
            ]);

            $sampleData = $this->getSampleData();
            $subject = $this->interpolate($request->subject, $sampleData);
            $bodyHtml = $request->body_html ? $this->interpolate($request->body_html, $sampleData) : '';
            $bodyText = $request->body_text ? $this->interpolate($request->body_text, $sampleData) : '';

            return response()->json([
                'subject'   => $subject,
                'body_html' => $bodyHtml,
                'body_text' => $bodyText,
            ]);
        } catch (\Throwable $e) {
            Log::error('NotificationTemplateController@preview error: ' . $e->getMessage());
            return response()->json(['message' => 'Preview failed.'], 500);
        }
    }

    /**
     * Send a test email using the provided template content.
     */
    public function testSend(Request $request)
    {
        try {
            $request->validate([
                'subject'   => 'required|string',
                'body_html' => 'required_without:body_text|string',
                'body_text' => 'required_without:body_html|string',
                'test_email' => 'required|email',
            ]);

            $sampleData = $this->getSampleData();
            $subject = $this->interpolate($request->subject, $sampleData);
            $html = $request->body_html ? $this->interpolate($request->body_html, $sampleData) : '';
            $text = $request->body_text ? $this->interpolate($request->body_text, $sampleData) : '';

            Mail::raw($text ?: strip_tags($html), function ($message) use ($subject, $html, $request) {
                $message->to($request->test_email)
                        ->subject($subject);
                if ($html) {
                    $message->html($html);
                }
            });

            Log::info('Test notification email sent.', ['to' => $request->test_email, 'subject' => $subject]);

            return response()->json(['message' => 'Test email sent successfully.']);
        } catch (\Throwable $e) {
            Log::error('NotificationTemplateController@testSend error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send test email.'], 500);
        }
    }

    /**
     * Delete a template.
     */
    public function destroy(int $id)
    {
        try {
            NotificationTemplate::findOrFail($id)->delete();
            Log::info('Notification template deleted.', ['template_id' => $id]);
            return redirect()->route('admin.notification-templates.index')->with('success', 'Template deleted.');
        } catch (\Throwable $e) {
            Log::error('NotificationTemplateController@destroy error: ' . $e->getMessage(), ['id' => $id]);
            return redirect()->route('admin.notification-templates.index')->with('error', 'Delete failed.');
        }
    }

    /**
     * Replace placeholders in a string with sample data.
     */
    private function interpolate(string $content, array $data): string
    {
        foreach ($data as $key => $value) {
            $content = str_replace('{{ ' . $key . ' }}', $value, $content);
            $content = str_replace('{{' . $key . '}}', $value, $content);
        }
        return $content;
    }

    /**
     * Provide sample data for preview and test.
     */
    private function getSampleData(): array
    {
        return [
            'user_name'       => 'John Doe',
            'scholarship_title' => 'Fully-Funded Masters Scholarship',
            'deadline'        => now()->addDays(7)->toDateString(),
            'match_score'     => '92%',
            'review_score'    => '8.5',
            'app_link'        => url('/dashboard'),
            'document_type'   => 'personal_statement',
        ];
    }
}