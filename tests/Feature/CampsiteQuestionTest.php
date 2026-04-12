<?php

namespace Tests\Feature;

use App\Models\Campsite;
use App\Models\CampsiteQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CampsiteQuestionTest extends TestCase
{
    use RefreshDatabase;

    // ---------------------------------------------------------------
    // 質問投稿 (store)
    // ---------------------------------------------------------------

    public function test_authenticated_user_can_post_question(): void
    {
        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->post(
            route('campsite.questions.store', $campsite),
            ['body' => '駐車場はありますか？']
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('campsite_questions', [
            'campsite_id' => $campsite->id,
            'user_id'     => $user->id,
            'body'        => '駐車場はありますか？',
        ]);
    }

    public function test_guest_cannot_post_question(): void
    {
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $response = $this->post(
            route('campsite.questions.store', $campsite),
            ['body' => '質問内容']
        );

        $response->assertRedirect(route('login'));
    }

    public function test_question_body_is_required(): void
    {
        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->post(
            route('campsite.questions.store', $campsite),
            ['body' => '']
        );

        $response->assertSessionHasErrors(['body']);
    }

    public function test_question_body_max_500_chars(): void
    {
        $user     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)->post(
            route('campsite.questions.store', $campsite),
            ['body' => str_repeat('あ', 501)]
        );

        $response->assertSessionHasErrors(['body']);
    }

    // ---------------------------------------------------------------
    // 回答投稿 (answer)
    // ---------------------------------------------------------------

    public function test_host_can_answer_question(): void
    {
        $host     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true, 'user_id' => $host->id]);

        $question = CampsiteQuestion::create([
            'campsite_id' => $campsite->id,
            'user_id'     => User::factory()->create()->id,
            'body'        => '質問内容',
        ]);

        $response = $this->actingAs($host)->patch(
            route('campsite.questions.answer', [$campsite, $question]),
            ['answer_body' => '回答内容です。']
        );

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('campsite_questions', [
            'id'          => $question->id,
            'answer_body' => '回答内容です。',
            'answered_by' => $host->id,
        ]);
    }

    public function test_admin_can_answer_question(): void
    {
        $admin    = User::factory()->create(['is_admin' => true]);
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $question = CampsiteQuestion::create([
            'campsite_id' => $campsite->id,
            'user_id'     => User::factory()->create()->id,
            'body'        => '質問内容',
        ]);

        $response = $this->actingAs($admin)->patch(
            route('campsite.questions.answer', [$campsite, $question]),
            ['answer_body' => '管理者からの回答です。']
        );

        $response->assertRedirect();
        $this->assertDatabaseHas('campsite_questions', [
            'id'          => $question->id,
            'answer_body' => '管理者からの回答です。',
        ]);
    }

    public function test_non_host_user_cannot_answer_question(): void
    {
        $other    = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true]);

        $question = CampsiteQuestion::create([
            'campsite_id' => $campsite->id,
            'user_id'     => User::factory()->create()->id,
            'body'        => '質問内容',
        ]);

        $response = $this->actingAs($other)->patch(
            route('campsite.questions.answer', [$campsite, $question]),
            ['answer_body' => '不正な回答']
        );

        $response->assertForbidden();
    }

    public function test_answer_body_is_required(): void
    {
        $host     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true, 'user_id' => $host->id]);

        $question = CampsiteQuestion::create([
            'campsite_id' => $campsite->id,
            'user_id'     => User::factory()->create()->id,
            'body'        => '質問内容',
        ]);

        $response = $this->actingAs($host)->patch(
            route('campsite.questions.answer', [$campsite, $question]),
            ['answer_body' => '']
        );

        $response->assertSessionHasErrors(['answer_body']);
    }

    public function test_answered_at_is_set_when_answer_is_posted(): void
    {
        $host     = User::factory()->create();
        $campsite = Campsite::factory()->create(['is_active' => true, 'user_id' => $host->id]);

        $question = CampsiteQuestion::create([
            'campsite_id' => $campsite->id,
            'user_id'     => User::factory()->create()->id,
            'body'        => '質問内容',
        ]);

        $this->assertFalse($question->isAnswered());

        $this->actingAs($host)->patch(
            route('campsite.questions.answer', [$campsite, $question]),
            ['answer_body' => '回答内容']
        );

        $this->assertTrue($question->fresh()->isAnswered());
    }
}
