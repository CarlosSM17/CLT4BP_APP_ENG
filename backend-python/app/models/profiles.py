# app/models/profiles.py
from pydantic import BaseModel, Field
from typing import Dict, List, Optional
from enum import Enum

class KnowledgeLevel(str, Enum):
    ALTO = "alto"
    MEDIO = "medio"
    BAJO = "bajo"

class MslqDimension(BaseModel):
    raw_score: float
    average: float
    level: KnowledgeLevel

class MotivationProfile(BaseModel):
    intrinsic_goal_orientation: MslqDimension
    extrinsic_goal_orientation: MslqDimension
    task_value: MslqDimension
    control_beliefs: MslqDimension
    self_efficacy: MslqDimension
    test_anxiety: MslqDimension

class CognitiveStrategies(BaseModel):
    rehearsal: MslqDimension
    elaboration: MslqDimension
    organization: MslqDimension
    critical_thinking: MslqDimension

class MetacognitiveStrategies(BaseModel):
    metacognitive_self_regulation: MslqDimension

class ResourceManagementStrategies(BaseModel):
    time_management: MslqDimension
    effort_regulation: MslqDimension
    peer_learning: MslqDimension
    help_seeking: MslqDimension

class StrategiesProfile(BaseModel):
    cognitive: CognitiveStrategies
    metacognitive: MetacognitiveStrategies
    resource_management: ResourceManagementStrategies

class MslqAnalysis(BaseModel):
    motivation: MotivationProfile
    strategies: StrategiesProfile
    summary: Dict

class TestAnalysis(BaseModel):
    total_questions: int
    correct_answers: int
    incorrect_answers: int
    percentage: float
    level: KnowledgeLevel
    incorrect_questions: List[int]

class KnowledgeAssessment(BaseModel):
    recall: TestAnalysis
    comprehension: TestAnalysis
    overall_level: KnowledgeLevel
    overall_percentage: float
    analysis: str

class ProfileSummary(BaseModel):
    overall_motivation: KnowledgeLevel
    overall_strategies: KnowledgeLevel
    prior_knowledge: KnowledgeLevel
    key_strengths: List[str]
    areas_for_support: List[str]

class StudentProfile(BaseModel):
    student_info: Dict
    mslq_analysis: MslqAnalysis
    knowledge_assessment: KnowledgeAssessment
    profile_summary: ProfileSummary
    recommendations: List[str]

class GroupProfile(BaseModel):
    mslq_averages: Dict
    knowledge_averages: Dict
    distribution: Dict
    group_summary: Dict
    teaching_recommendations: List[str]
    student_count: int