package pt.ipleiria.estg.dei.amsi.cinelive;

import android.os.Bundle;

import androidx.annotation.NonNull;
import androidx.annotation.Nullable;
import androidx.fragment.app.Fragment;

import android.view.LayoutInflater;
import android.view.View;
import android.view.ViewGroup;

import pt.ipleiria.estg.dei.amsi.cinelive.databinding.FragmentFilmesBinding;

/**
 * A simple {@link Fragment} subclass.
 * Use the {@link FilmesFragment#newInstance} factory method to
 * create an instance of this fragment.
 */
public class FilmesFragment extends Fragment {
    private FragmentFilmesBinding binding;

    @Override
    public View onCreateView(@NonNull LayoutInflater inflater, ViewGroup container, Bundle savedInstanceState) {
        binding = FragmentFilmesBinding.inflate(inflater, container, false);
        return binding.getRoot();
    }

    @Override
    public void onViewCreated(@NonNull View view, @Nullable Bundle savedInstanceState) {
        binding.textTitle.setText("Filmes");
    }

    @Override
    public void onDestroyView() {
        super.onDestroyView();
        binding = null;
    }
}