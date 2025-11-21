package pt.ipleiria.estg.dei.amsi.cinelive;

import android.content.Intent;
import android.os.Bundle;

import androidx.activity.EdgeToEdge;
import androidx.appcompat.app.AppCompatActivity;
import androidx.core.graphics.Insets;
import androidx.core.view.ViewCompat;
import androidx.core.view.WindowInsetsCompat;

import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivityLoginBinding;
import pt.ipleiria.estg.dei.amsi.cinelive.databinding.ActivitySignupBinding;

public class SignupActivity extends AppCompatActivity {

    ActivitySignupBinding binding;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        EdgeToEdge.enable(this);

        binding = ActivitySignupBinding.inflate(getLayoutInflater());
        setContentView(binding.getRoot());

        ViewCompat.setOnApplyWindowInsetsListener(findViewById(R.id.main), (v, insets) -> {
            Insets systemBars = insets.getInsets(WindowInsetsCompat.Type.systemBars());
            v.setPadding(systemBars.left, systemBars.top, systemBars.right, systemBars.bottom);
            return insets;
        });

        // Seta voltar atrás
        setSupportActionBar(binding.toolbar.topAppBar);
        getSupportActionBar().setDisplayHomeAsUpEnabled(true);

        binding.btnLogin.setOnClickListener(v -> {
            startActivity(new Intent(SignupActivity.this, LoginActivity.class));
            finish();
        });
    }

    // Voltar atrás
    @Override
    public boolean onSupportNavigateUp() {
        finish();
        return true;
    }

}